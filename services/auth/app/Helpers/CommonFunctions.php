<?php
use App\Models\Address;
use Hashids\Hashids;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
	/*
	 *
	 * Encode Id to a Hashids\Hashids
	 *
	 * ------------------------------------------------------------------------
	 */
	if (! function_exists('encode_id')) {
		/**
		 * Prepare the Column Name for Lables.
		 */
		function encode_id($id)
		{
           $hashids = new Hashids('dash_ins', 13, 'abcdefghijklmnopqrstuvwxyz');
           return $hashids->encode($id); 
		}
	}

	/*
	 *
	 * Decode Id to a Hashids\Hashids
	 *
	 * ------------------------------------------------------------------------
	 */
	if (! function_exists('decode_id')) {
		/**
		 * Prepare the Column Name for Lables.
		 */
		function decode_id($hashid)
		{
			if(!isset($hashid)){
				return NULL;
			}
			$hashids =  new Hashids('dash_ins', 13, 'abcdefghijklmnopqrstuvwxyz');
			$id = $hashids->decode($hashid);
			
			if (count($id)) {
				return $id[0];
			}
			return NULL;
		}
	}
  
	if (!function_exists('convertToYmdFormat')) {
		/**
		 * Convert date from dd/mm/yyyy to Y-m-d format.
		 *
		 * @param string $date
		 * @return string|null
		 */
		function convertToYmdFormat($date , $inputFormat = "d/m/Y", $outputFormat = "Y-m-d")
		{
			try {
				return \Carbon\Carbon::createFromFormat($inputFormat, $date)->format($outputFormat);
			} catch (\Exception $e) {
				// Return null if the date is invalid
				return null;
			}
		}
	}
    
	if (!function_exists('uploadFile')) {
		function uploadFile($file, string $directory)
		{
			$user_id=Auth::user()->id??0;
			$filename =$user_id.time() . '.' . $file->getClientOriginalExtension();
			Storage::disk('public')->put($directory . '/' . $filename, file_get_contents($file));
			return $filename;
		}
	}


	if (!function_exists('wrapText')) {
		function wrapText($text)
		{
			return wordwrap($text, 50, "\n");
		}
	}
	 

	if (!function_exists('callserviceone')) {
		function callserviceone($service)
		{
			$requestPath = request()->path();
            $serviceUrl='';
            if($service==config('admin.policy_service_name')){
                $serviceUrl = config('admin.policy_url') . '/' . $requestPath;
            }
			/*dd(request()->headers->all());*/
			$headers = [
				'X-User-Id' => auth()->id(),
				'X-User-Email' => auth()->user()->email ?? '',
				'X-Role-Id' => auth()->user()->roles->first()->id ?? '',
				'X-Role-Name' => auth()->user()->roles->first()->name ?? '',
			];
			
            // Send the request to the Policy Service
            $response = Http::withHeaders($headers)->send(request()->method(), $serviceUrl, [
            'query' => request()->query(), 
            'json' => request()->isMethod('post') ? request()->all() : null,
            ]);
			return response()->json($response->json(), $response->status());
		}
	}
	if (!function_exists('callservice')) {
		function callservice($service)
		{
			$requestPath = request()->path();
			$serviceUrl = '';
	
			// Check if the service name exists in the configuration
			if ($service == config('admin.policy_service_name')) {
				$serviceUrl = config('admin.policy_url') . '/' . $requestPath;
			} else {
				return response()->json(['error' => 'Service not found'], 404);
			}
	
			// Verify user authentication
			if (!auth()->check()) {
				return response()->json(['error' => 'Unauthorized'], 401);
			}
	
			// Prepare additional headers
			$additionalHeaders = [
				'auth' =>base64_encode(json_encode([
					'user_id' => auth()->id(),
					'user_email' => auth()->user()->email ?? '',
					'role_id' => auth()->user()->roles->first()->id ?? '',
					'role_name' => auth()->user()->roles->first()->name ?? '',
				])),
			];
			//dd(request()->headers->all());
			$headers = [
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
			];
			// Send the request to the Policy Service
			try {
				
				$response = Http::withHeaders(array_merge($headers, $additionalHeaders))
				->send(request()->method(), $serviceUrl, [
					//'query' => request()->query(),
					//'form_params' => request()->all(),
					//'json' => request()->json(),
					'body'=>json_encode(request()->all()),
					'timeout' => 10,
				]);
	
				// Check if the response is successful
				if ($response->successful()) {
					return response()->json($response->json(), $response->status());
				} else {
					return response()->json(['error' => $response->json()], $response->status());
				}
	
			} catch (\Exception $e) {
				return response()->json(['error' => 'Request failed: ' . $e->getMessage()], 500);
			}
		}
  }