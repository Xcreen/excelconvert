<?php
namespace ExcelConvert\Controller;

use ExcelConvert\Model\JobModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class HomeController extends AbstractController
{

    /**
     * Display Home/Upload Page
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function index(Request $request, Response $response, $args): Response
    {
        return $this->twig->render($response, 'upload.html.twig', []);
    }

    public function showDownloadPage(Request $request, Response $response, $args): Response
    {
        //Check if Job-ID exists
        if(!array_key_exists('public_job_id', $args)) {
            $response->withStatus(404);
            return $this->twig->render($response, 'download.html.twig', [
                'errormessage' => 'This job do not exist!'
            ])->withStatus(404);
        }
        //Check if Job and File exists
        $jobModel = new JobModel($this->container);
        $job = $jobModel->getJobByPublicID($args['public_job_id']);
        if(!$job) {
            return $this->twig->render($response, 'download.html.twig', [
                'errormessage' => 'This job do not exist!'
            ])->withStatus(404);
        }
        if(!file_exists($_SERVER['DOCUMENT_ROOT'] . '/../var/downloads/' .  $job['public_job_id'] . '.csv')) {
            $response->withStatus(404);
            return $this->twig->render($response, 'download.html.twig', [
                'errormessage' => 'This job do not exist!'
            ])->withStatus(404);
        }
        return $this->twig->render($response, 'download.html.twig', [
            'jobid' => $args['public_job_id']
        ]);
    }
}
