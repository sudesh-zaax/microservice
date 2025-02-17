<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventXss
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $input = $request->all();

        array_walk_recursive($input, function(&$input , $key) {
            if(!empty($input))
            {
                    $input = preg_replace('/[^a-zA-Z0-9@.#,\/!$%^&*()-+=[]:?{}\\|_"\']/', '', $input);
                    $input = strip_tags($input);
                    $input = str_replace('script', '', $input);
                    $input = str_replace('alert', '', $input);
                    $input = str_replace('prompt', '', $input);
                    $input = str_replace('onmouseover', '', $input);
                    $input = str_replace('javascript', '', $input);
            }
        });

        $request->merge($input);

        return $next($request);
    }

   
}
