<?php

namespace Botble\Logistics\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Logistics\Usecase\WebhookUsecase;
use Illuminate\Http\Request;

class WebhookController extends BaseController
{
    public function __construct(
     ){}

    public function webhook(Request $request, string $provider, WebhookUsecase $webhookUsecase )
    {
        return $webhookUsecase->webhook($request->all(), $provider);
    }
}