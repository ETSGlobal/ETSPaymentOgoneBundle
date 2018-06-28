<?php

namespace ETS\Payment\OgoneBundle\Plugin\Configuration;

use JMS\Payment\CoreBundle\Model\ExtendedDataInterface;

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
 * Design class
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
     * @param null $tp
     * @param null $title
     * @param null $bgColor
     * @param null $txtColor
     * @param null $tblBgColor
     * @param null $tblTxtColor
     * @param null $buttonBgColor
     * @param null $buttonTxtColor
     * @param null $fontType
     * @param null $logo
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
     * @param ExtendedDataInterface $data
     *
     * @return string|null
     */
    public function getTp(ExtendedDataInterface $data): ?string
    {
        return $data->has('tp') ? $data->get('tp') : $this->tp;
    }

    /**
     * @param ExtendedDataInterface $data
     *
     * @return string|null
     */
    public function getTitle(ExtendedDataInterface $data): ?string
    {
        return $data->has('title') ? $data->get('title') : $this->title;
    }

    /**
     * @param ExtendedDataInterface $data
     *
     * @return string|null
     */
    public function getBgColor(ExtendedDataInterface $data): ?string
    {
        return $data->has('bgColor') ? $data->get('bgColor') : $this->bgColor;
    }

    /**
     * @param ExtendedDataInterface $data
     *
     * @return string|null
     */
    public function getTxtColor(ExtendedDataInterface $data): ?string
    {
        return $data->has('txtColor') ? $data->get('txtColor') : $this->txtColor;
    }

    /**
     * @param ExtendedDataInterface $data
     *
     * @return string|null
     */
    public function getTblBgColor(ExtendedDataInterface $data): ?string
    {
        return $data->has('tblBgColor') ? $data->get('tblBgColor') : $this->tblBgColor;
    }

    /**
     * @param ExtendedDataInterface $data
     *
     * @return string|null
     */
    public function getTblTxtColor(ExtendedDataInterface $data): ?string
    {
        return $data->has('tblTxtColor') ? $data->get('tblTxtColor') : $this->tblTxtColor;
    }

    /**
     * @param ExtendedDataInterface $data
     *
     * @return string|null
     */
    public function getButtonBgColor(ExtendedDataInterface $data): ?string
    {
        return $data->has('buttonBgColor') ? $data->get('buttonBgColor') : $this->buttonBgColor;
    }

    /**
     * @param ExtendedDataInterface $data
     *
     * @return string|null
     */
    public function getButtonTxtColor(ExtendedDataInterface $data): ?string
    {
        return $data->has('buttonTxtColor') ? $data->get('buttonTxtColor') : $this->buttonTxtColor;
    }

    /**
     * @param ExtendedDataInterface $data
     *
     * @return string|null
     */
    public function getFontType(ExtendedDataInterface $data): ?string
    {
        return $data->has('fontType') ? $data->get('fontType') : $this->fontType;
    }

    /**
     * @param ExtendedDataInterface $data
     *
     * @return string|null
     */
    public function getLogo(ExtendedDataInterface $data): ?string
    {
        return $data->has('logo') ? $data->get('logo') : $this->logo;
    }

    /**
     * @param ExtendedDataInterface $data
     *
     * @return array
     */
    public function getRequestParameters(ExtendedDataInterface $data): array
    {
        return [
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
        ];
    }
}
