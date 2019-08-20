<?php
namespace ExcelConvert\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class IEController extends AbstractController
{

    /**
     * Display IE-Error Page
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function index(Request $request, Response $response, $args): Response
    {
        return $this->twig->render($response, 'ie-error.html.twig', [
            //"lang" => "de"
        ]);
    }
}
