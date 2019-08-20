<?php

use DI\Container;
use ExcelConvert\Model\JobModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

require __DIR__ . '/vendor/autoload.php';

$eol = chr(0x0D) . chr(0x0A);
$container = new Container();

while(true) {
    echo 'Checking for new jobs ...'.$eol;
    try {
        $container->set('pdoservice', function () {
            return new PDO('mysql:host=localhost;dbname=excelconvert', 'excelconvert', 'excelconvert');
        });
        $jobModel = new JobModel($container);

        $jobs = $jobModel->getJobsByStatus('pending');
        if (count($jobs) > 0) {
            $jobID = $jobs[0]['job_id'];
            echo 'Start processing: ' . $jobID . $eol;
            $jobModel->setStatus($jobID, 'in_progress');
            try {
                $filename = $jobs[0]['public_job_id'] . '.' . $jobs[0]['fileextension'];
                $reader = IOFactory::createReaderForFile('var/uploads/' . $filename);

                $reader->setReadDataOnly(true);
                $spreadsheet = $reader->load('var/uploads/' . $filename);

                $writer = new Csv($spreadsheet);
                $writer->setUseBOM($jobs[0]['option_include_utf8bom']);
                $writer->setDelimiter($jobs[0]['option_delimiter']);
                $writer->setLineEnding("\r\n");
                $writer->save('var/downloads/' . $jobs[0]['public_job_id'] . '.' . 'csv');
                $jobModel->setStatus($jobID, 'finished');
                echo 'Job finished!' . $eol;
            }
            catch (Exception $ex) {
                $jobModel->setStatus($jobID, 'failed');
                $jobModel->setFailedInformation($jobID, $ex->getMessage());
                echo 'Job failed: ' . $ex->getMessage().$eol;
            }
        }
        else {
            sleep(5);
        }
    }
    catch (Exception $ex){
        echo $ex->getMessage().$eol;
        sleep(5);
    }
}
