<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Traits\GoogleAssitans;

class TestController extends Controller
{
  use GoogleAssitans;

  public function test(Request $request)
  {
    return redirect($request->redirect_uri . '?code='. config('token.code') . '=' . $request->state);
//    dd($request->all());
  }

  public function token(Request $request): JsonResponse
  {
    $data = [
      "token_type" => "Bearer",
      "access_token" => "1836.15267389",
      "expires_in" => 36000
    ];

    return response()->json($data);
  }

  public function google_assistant (Request $request): JsonResponse
  {
    $request->validate([
      'inputs' => 'required|array',
    ]);

    $requestArray = $request->all();

    if ($requestArray['inputs'][0]['intent'] === 'action.devices.SYNC') {
      $data = $this->sync($request);
    } else if ($requestArray['inputs'][0]['intent'] === 'action.devices.QUERY') {
      $data = $this->query($request);
    } else {
      $data = [];
    }

    return response()->json($data);
  }
}
