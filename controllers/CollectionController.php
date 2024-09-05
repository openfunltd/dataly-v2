<?php

class CollectionController extends MiniEngine_Controller
{
    public function tableAction($type)
    {
        $this->view->type = $type;
        $this->view->data = LYAPI::apiQuery("/{$type}s", "抓取 {$type} 的資料");
    }
}
