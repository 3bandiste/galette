<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Groups list PDF
 *
 * PHP version 5
 *
 * Copyright © 2016 The Galette Team
 *
 * This file is part of Galette (https://galette.eu).
 *
 * Galette is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Galette is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Galette. If not, see <http://www.gnu.org/licenses/>.
 *
 * @category  IO
 * @package   Galette
 *
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2016 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      https://galette.eu
 * @since     Available since 0.9dev - 2016-09-11
 */

namespace Galette\IO;

use Galette\Core\Preferences;
use Galette\Core\PrintLogo;
use Analog\Analog;
use Galette\Core\Login;

/**
 * Groups list PDF
 *
 * @category  IO
 * @name      PDF
 * @package   Galette
 * @abstract  Class for expanding TCPDF.
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2016 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      https://galette.eu
 * @since     Available since 0.9dev - 2016-09-11
 */

class PdfGroups extends Pdf
{
    public const SHEET_FONT = self::FONT_SIZE - 2;

    private $doc_title;

    /**
     * Main constructor, set creator and author
     *
     * @param Preferences $prefs Preferences
     */
    public function __construct(Preferences $prefs)
    {
        parent::__construct($prefs);
        $this->filename = __('groups_list') . '.pdf';
        $this->init();
    }

    /**
     * Draws PDF page Header
     *
     * @return void
     */
    public function Header() // phpcs:ignore PSR1.Methods.CamelCapsMethodName
    {
        $this->Cell(
            0,
            10,
            _T("Members by groups"),
            0,
            0,
            'C',
            false,
            '',
            0,
            false,
            'M',
            'M'
        );
    }

    /**
     * Initialize PDF
     *
     * @return void
     */
    private function init()
    {
        // Set document information
        $this->doc_title = _T("Members by groups");
        $this->SetTitle($this->doc_title);
        $this->SetSubject(_T("Generated by Galette"));

        // Enable Auto Page breaks
        $this->SetAutoPageBreak(true, 20);

        // Set colors
        $this->SetTextColor(0, 0, 0);

        // Set margins
        $this->setMargins(10, 20);
        $this->setHeaderMargin(10);

        // Set font
        $this->SetFont(self::FONT, '', self::SHEET_FONT);

        //enable pagination
        $this->showPagination();
    }

    /**
     * Draw file
     *
     * @param array $groups Groups list
     * @param Login $login  Login instance
     *
     * @return void
     */
    public function draw($groups, Login $login)
    {
        $this->Open();
        $this->AddPage();
        $this->PageHeader($this->doc_title);

        $first = true;
        foreach ($groups as $group) {
            $id = $group->getId();
            if (!$login->isGroupManager($id)) {
                Analog::log(
                    'Trying to export group ' . $id . ' as PDF without appropriate permissions',
                    Analog::INFO
                );
                continue;
            }
            // Header
            if ($first === false) {
                $this->ln(5);
            }
            $this->SetFont('', 'B', self::SHEET_FONT + 1);
            $this->Cell(190, 4, $group->getName(), 0, 1, 'C');
            $this->SetFont('', '', self::SHEET_FONT);

            $managers_list = $group->getManagers();
            $managers = array();
            foreach ($managers_list as $m) {
                $managers[] = $m->sfullname;
            }
            if (count($managers) > 0) {
                $this->Cell(
                    190,
                    4,
                    _T("Managers:") . ' ' . implode(', ', $managers),
                    0,
                    1,
                    ($this->i18n->isRTL() ? 'L' : 'R')
                );
            }
            $this->ln(3);

            $this->SetFont('', 'B');
            $this->SetFillColor(255, 255, 255);
            $this->Cell(80, 7, _T("Name"), 1, 0, 'C', true);
            $this->Cell(50, 7, _T("Email"), 1, 0, 'C', true);
            $this->Cell(30, 7, _T("Phone"), 1, 0, 'C', true);
            $this->Cell(30, 7, _T("GSM"), 1, 1, 'C', true);

            $this->SetFont('', 'B');

            $members = $group->getMembers();

            foreach ($members as $m) {
                $align = ($this->i18n->isRTL() ? 'R' : 'L');
                $this->Cell(80, 7, $m->sname, 1, 0, $align);
                $this->Cell(50, 7, $m->email, 1, 0, $align);
                $this->Cell(30, 7, $m->phone, 1, 0, $align);
                $this->Cell(30, 7, $m->gsm, 1, 1, $align);
            }
            $this->Cell(190, 0, '', 'T');
            $first = false;
        }
    }
}
