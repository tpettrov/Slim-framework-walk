<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
/**
 * Created by PhpStorm.
 * User: apetrov
 * Date: 11/10/2017
 * Time: 10:46 AM
 */
class stopNonAnton
{

    public function __invoke(Request $request, Response $response, $next)
    {
        $response->getBody()->write('BEFORE');
        $path = $request->getUri()->getPath();

        if (substr($path, -5) != 'anton') {
            $response->getBody()->write('Sorry you are not my master Anton. I cannot greet you!');
            $response = $response->withStatus(404);
        } else {
            $response = $next($request, $response);
            $response->getBody()->write('AFTER');
        }

        return $response;
    }

}