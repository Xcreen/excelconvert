<?php
namespace ExcelConvert\Model;

class JobModel extends AbstractModel
{
    /**
     * Creates a job
     * @param $filename - Original Filename
     * @return mixed
     */
    public function createJob($filename, $fileExtension, $includeUtf8bom, $delimiter)
    {
        //Insert Job
        $statement = $this->pdo->prepare('INSERT INTO jobs (public_job_id, filename, fileextension, option_include_utf8bom, option_delimiter) VALUES (UUID(), :filename, :fileextension, :option_include_utf8bom, :option_delimiter)');
        $statement->execute([
            'filename' => $filename,
            'fileextension' => $fileExtension,
            'option_include_utf8bom' => $includeUtf8bom,
            'option_delimiter' => $delimiter
        ]);
        $jobID = $this->pdo->lastInsertId();
        //Get Public UUID from Job
        $selectStatement = $this->pdo->prepare('SELECT public_job_id FROM jobs WHERE job_id = :jobid');
        $selectStatement->execute(['jobid' => $jobID]);
        $job = $selectStatement->fetch();
        return $job['public_job_id'];
    }

    /**
     * Get job by Public-ID
     * @param $publicID
     * @return
     */
    public function getJobByPublicID($publicID)
    {
        $statement = $this->pdo->prepare('SELECT * FROM jobs WHERE public_job_id = :public_job_id');
        $statement->execute(['public_job_id' => $publicID]);
        return $statement->fetch();
    }

    /**
     * Get all jobs by status
     * @param $status
     * @return mixed
     */
    public function getJobsByStatus($status)
    {
        $statement = $this->pdo->prepare('SELECT * FROM jobs WHERE job_status = :job_status');
        $statement->execute(['job_status' => $status]);
        return $statement->fetchAll();
    }

    /**
     * Set status of a job
     * @param $id
     * @param $status
     */
    public function setStatus($id, $status)
    {
        switch ($status) {
            case 'in_progress':
                $statement = $this->pdo->prepare('UPDATE jobs SET job_status = :job_status, date_start = NOW() WHERE job_id = :job_id');
                break;
            case 'finished':
            case 'failed':
                $statement = $this->pdo->prepare('UPDATE jobs SET job_status = :job_status, date_finished = NOW() WHERE job_id = :job_id');
                break;
            default:
                $statement = $this->pdo->prepare('UPDATE jobs SET job_status = :job_status WHERE job_id = :job_id');
                break;
        }
        return $statement->execute(['job_status' => $status, 'job_id' => $id]);
    }

    public function setFailedInformation($id, $information)
    {
        $statement = $this->pdo->prepare('UPDATE jobs SET failed_information = :failed_information WHERE job_id = :job_id');
        return $statement->execute(['failed_information' => $information, 'job_id' => $id]);
    }
}
