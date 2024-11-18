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

        if (method_exists($this, "list_{$type}_{$tab}")) {
            $this->{"list_{$type}_{$tab}"}();
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

        if (method_exists($this, "item_{$type}_{$tab}")) {
            $this->{"item_{$type}_{$tab}"}();
        }
    }

    public function list_ivod_datelist()
    {
    }
}
