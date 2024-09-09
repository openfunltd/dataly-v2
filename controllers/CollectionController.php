<?php

class CollectionController extends MiniEngine_Controller
{
    public function listAction($type, $tab = null)
    {
        $this->view->type = $type;
        $this->view->features = TypeHelper::getCollectionFeatures($type);
        if (!$tab) {
            $tab = key($this->view->features);
        }
        $this->view->tab = $tab;
        if (!array_key_exists($tab, $this->view->features)) {
            throw new Exception('Invalid tab: ' . $tab);
        }
    }

    public function itemAction($type, $id, $tab = null)
    {
        $this->view->type = $type;
        $this->view->id = $id;
        $this->view->data = TypeHelper::getDataByID($type, $id);
        $this->view->features = TypeHelper::getItemFeatures($type);
        if (!$tab) {
            $tab = key($this->view->features);
        }
        $this->view->tab = $tab;
        if (!array_key_exists($tab, $this->view->features)) {
            throw new Exception('Invalid tab: ' . $tab);
        }
    }
}
