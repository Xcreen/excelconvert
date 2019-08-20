<?php
namespace ExcelConvert\Controller;

use ExcelConvert\Model\JobModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Stream;

class JobController extends AbstractController
{

    /**
     * Creates a new Job and uploads the excel-file
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response (Job-ID)
     */
    public function upload(Request $request, Response $response, $args): Response
    {
        $postData = $request->getParsedBody();
        $uploadedFiles = $request->getUploadedFiles();
        //Check if Utf-8Bom
        if(!array_key_exists('includeUTF8Bom', $postData)) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'Option missing!']));
            return $response->withStatus(400, 'Option missing')
                ->withHeader('Content-Type', 'application/json');
        }
        $includeUTF8 = 0;
        if($postData['includeUTF8Bom'] === 'true') {
            $includeUTF8 = 1;
        }
        //Check Delimiter
        if(!array_key_exists('delimiter', $postData)) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'Option missing!']));
            return $response->withStatus(400, 'Option missing')
                ->withHeader('Content-Type', 'application/json');
        }
        //Check if Excel-File exists
        if(!array_key_exists('file', $uploadedFiles)) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'No file was updated!']));
            return $response->withStatus(400, 'No file uploaded')
                ->withHeader('Content-Type', 'application/json');
        }

        $excelFile = $uploadedFiles['file'];
        $fileName = $excelFile->getClientFilename();
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
        //Check if Extension is correct
        if($fileExtension !== 'xls' && $fileExtension !== 'xlsx') {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'Incorrect excel-file!']));
            return $response->withStatus(400, 'Invalid file')
                ->withHeader('Content-Type', 'application/json');
        }
        //Create Job
        $jobModel = new JobModel($this->container);
        $publicJobID = $jobModel->createJob($fileName, $fileExtension, $includeUTF8, $postData['delimiter']);
        //Move Excel-File
        $newFileName = $publicJobID . '.' . $fileExtension;
        $excelFile->moveTo(__DIR__ . '../../../var/uploads/' . $newFileName);

        $response->getBody()->write(json_encode(['success' => true, 'job_id' => $publicJobID]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function download(Request $request, Response $response, $args): Response
    {
        //Check if Job-ID exists
        if(!array_key_exists('public_job_id', $args)) {
            return $this->twig->render($response, 'download.html.twig', [
                'errormessage' => 'This file do not exist!'
            ])->withStatus(404);
        }
        //Check if Job and File exists
        $jobModel = new JobModel($this->container);
        $job = $jobModel->getJobByPublicID($args['public_job_id']);
        if(!$job) {
            return $this->twig->render($response, 'download.html.twig', [
                'errormessage' => 'This file do not exist!'
            ])->withStatus(404);
        }

        $filePath = __DIR__ . '../../../var/downloads/' .  $job['public_job_id'] . '.csv';
        if(!file_exists($filePath)) {
            return $this->twig->render($response, 'download.html.twig', [
                'errormessage' => 'This file do not exist!'
            ])->withStatus(404);
        }
        $fileStream = fopen($filePath, 'rb');
        return $response->withBody(new Stream($fileStream))
            ->withHeader('Content-Disposition', 'attachment; filename=' . $job['public_job_id'] . '.csv;')
            ->withHeader('Content-Type', mime_content_type($filePath))
            ->withHeader('Content-Length', filesize($filePath));
    }

    /**
     * Get Status of the job
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function status(Request $request, Response $response, $args): Response
    {
        //Check if Job-ID exists
        if(!array_key_exists('public_job_id', $args)) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'No job found!']));
            return $response->withStatus(400, 'No job found')
                ->withHeader('Content-Type', 'application/json');
        }
        //Get status from job
        $jobModel = new JobModel($this->container);
        $job = $jobModel->getJobByPublicID($args['public_job_id']);
        if(!$job) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'No job found!']));
            return $response->withStatus(400, 'No job found')
                ->withHeader('Content-Type', 'application/json');
        }

        $response->getBody()->write(json_encode(['success' => true, 'status' => $job['job_status']]));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
