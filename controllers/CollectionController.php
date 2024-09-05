<?php

class CollectionController extends MiniEngine_Controller
{
    public function tableAction($type)
    {
        $this->view->type = $type;
        $this->view->data = TypeHelper::getDataFromAPI($type);
        $this->view->aggs = TypeHelper::getCurrentAgg($type);
    }
}
