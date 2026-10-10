<?php

class TestablePs_ImageSlider extends Ps_ImageSlider
{
    public function callValidateUrl($link)
    {
        return $this->validateUrl($link);
    }

    public function callPostValidation()
    {
        return $this->_postValidation();
    }

    public function callPostProcess()
    {
        return $this->_postProcess();
    }

    public function callClearCacheRecord()
    {
        $this->clearCache();
    }

    public function callGetWarningMultishopHtml()
    {
        return $this->getWarningMultishopHtml();
    }

    public function callGetCurrentShopInfoMsg()
    {
        return $this->getCurrentShopInfoMsg();
    }

    public function callGetShopContextError($name, $mode)
    {
        return $this->getShopContextError($name, $mode);
    }

    public function callGetShopAssociationError($id)
    {
        return $this->getShopAssociationError($id);
    }
}
