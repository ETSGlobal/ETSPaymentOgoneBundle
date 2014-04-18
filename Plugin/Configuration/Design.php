<?php

namespace ETS\Payment\OgoneBundle\Plugin\Configuration;

use JMS\Payment\CoreBundle\Entity\ExtendedData;

/*
 * Copyright 2013 ETSGlobal <ecs@etsglobal.org>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * Redirection class
 *
 * @author ETSGlobal <ecs@etsglobal.org>
 */
class Design
{

    protected $tp;
    protected $title;
    protected $bgColor;
    protected $txtColor;
    protected $tblBgColor;
    protected $tblTxtColor;
    protected $buttonBgColor;
    protected $buttonTxtColor;
    protected $fontType;
    protected $logo;

    /**
     * @param string $acceptUrl
     * @param string $declineUrl
     * @param string $exceptionUrl
     * @param string $cancelUrl
     * @param string $backUrl
     */
    public function __construct($tp = null, $title = null, $bgColor = null, $txtColor = null, $tblBgColor = null, $tblTxtColor = null, $buttonBgColor = null, $buttonTxtColor = null, $fontType = null, $logo = null)
    {
        $this->tp             = $tp;
        $this->title          = $title;
        $this->bgColor        = $bgColor;
        $this->txtColor       = $txtColor;
        $this->tblBgColor     = $tblBgColor;
        $this->tblTxtColor    = $tblTxtColor;
        $this->buttonBgColor  = $buttonBgColor;
        $this->buttonTxtColor = $buttonTxtColor;
        $this->fontType       = $fontType;
        $this->logo           = $logo;
    }

    /**
     * @param \JMS\Payment\CoreBundle\Entity\ExtendedData $data
     *
     * @return string|null
     */
    public function getTp(ExtendedData $data)
    {
    	return $data->has('tp') ? $data->get('tp') : $this->tp;
    }

    /**
     * @param \JMS\Payment\CoreBundle\Entity\ExtendedData $data
     *
     * @return string|null
     */
    public function getTitle(ExtendedData $data)
    {
        return $data->has('title') ? $data->get('title') : $this->title;
    }

    /**
     * @param \JMS\Payment\CoreBundle\Entity\ExtendedData $data
     *
     * @return string|null
     */
    public function getBgColor(ExtendedData $data)
    {
        return $data->has('bgColor') ? $data->get('bgColor') : $this->bgColor;
    }

    /**
     * @param \JMS\Payment\CoreBundle\Entity\ExtendedData $data
     *
     * @return string|null
     */
    public function getTxtColor(ExtendedData $data)
    {
        return $data->has('txtColor') ? $data->get('txtColor') : $this->txtColor;
    }

    /**
     * @param \JMS\Payment\CoreBundle\Entity\ExtendedData $data
     *
     * @return string|null
     */
    public function getTblBgColor(ExtendedData $data)
    {
        return $data->has('tblBgColor') ? $data->get('tblBgColor') : $this->tblBgColor;
    }

    /**
     * @param \JMS\Payment\CoreBundle\Entity\ExtendedData $data
     *
     * @return string|null
     */
    public function getTblTxtColor(ExtendedData $data)
    {
        return $data->has('tblTxtColor') ? $data->get('tblTxtColor') : $this->tblTxtColor;
    }

    /**
     * @param \JMS\Payment\CoreBundle\Entity\ExtendedData $data
     *
     * @return string|null
     */
    public function getButtonBgColor(ExtendedData $data)
    {
        return $data->has('buttonBgColor') ? $data->get('buttonBgColor') : $this->buttonBgColor;
    }

    /**
     * @param \JMS\Payment\CoreBundle\Entity\ExtendedData $data
     *
     * @return string|null
     */
    public function getButtonTxtColor(ExtendedData $data)
    {
        return $data->has('buttonTxtColor') ? $data->get('buttonTxtColor') : $this->buttonTxtColor;
    }

    /**
     * @param \JMS\Payment\CoreBundle\Entity\ExtendedData $data
     *
     * @return string|null
     */
    public function getFontType(ExtendedData $data)
    {
        return $data->has('fontType') ? $data->get('fontType') : $this->fontType;
    }

    /**
     * @param \JMS\Payment\CoreBundle\Entity\ExtendedData $data
     *
     * @return string|null
     */
    public function getLogo(ExtendedData $data)
    {
        return $data->has('logo') ? $data->get('logo') : $this->logo;
    }

    /**
     * @param \JMS\Payment\CoreBundle\Entity\ExtendedData $data
     *
     * @return string|null
     */
    public function getRequestParameters(ExtendedData $data)
    {
        return array(
            'TP'             => $this->getTp($data),
            'TITLE'          => $this->getTitle($data),
            'BGCOLOR'        => $this->getBgColor($data),
            'TXTCOLOR'       => $this->getTxtColor($data),
            'TBLBGCOLOR'     => $this->getTblBgColor($data),
            'TBLTXTCOLOR'    => $this->getTblTxtColor($data),
            'BUTTONBGCOLOR'  => $this->getButtonBgColor($data),
            'BUTTONTXTCOLOR' => $this->getButtonTxtColor($data),
            'FONTTYPE'       => $this->getFontType($data),
            'LOGO'           => $this->getLogo($data),
        );
    }
}
