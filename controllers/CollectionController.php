<?php

class CollectionController extends MiniEngine_Controller
{
    public function tableAction($type)
    {
        $this->view->type = $type;
    }

    public function itemAction($type, $id)
    {
        $this->view->type = $type;
        $this->view->id = $id;
        $this->view->data = TypeHelper::getDataByID($type, $id);
    }
}
