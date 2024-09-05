<?php

class CollectionController extends MiniEngine_Controller
{
    public function tableAction($type)
    {
        $this->view->type = $type;
    }
}
