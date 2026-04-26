<?php

namespace App\Http\Controllers\Admin;

use A17\Twill\Helpers\BlockRenderer;
use App\Services\TwillBlockService;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\View\Factory as ViewFactory;

class BlocksController extends \A17\Twill\Http\Controllers\Admin\BlocksController
{
    public function __construct(
        private TwillBlockService $blockService,
    ) {
        parent::__construct();
    }

    public function preview(
        Application $app,
        ViewFactory $_viewFactory,
        Request $request,
    ): string {
        if ($request->has('activeLanguage')) {
            $app->setLocale($request->get('activeLanguage'));
        }

        $data = $request->except('activeLanguage');

        $renderer = BlockRenderer::fromCmsArray($data);
        $rootBlock = $renderer->rootBlocks[0]->renderData->block;
        $block = $this->blockService->formatBlock($rootBlock);

        return view('admin.block-preview', ['block' => $block])->render();
    }
}
