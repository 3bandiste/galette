<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Export and download an export file
 *
 * PHP version 5
 *
 * Copyright © 2013 The Galette Team
 *
 * This file is part of Galette (http://galette.tuxfamily.org).
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
 * @category  Main
 * @package   Galette
 *
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2013 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @version   SVN: $Id$
 * @link      http://galette.tuxfamily.org
 * @since     Available since 0.7.4dev - 2013-01-31
 */

use Analog\Analog as Analog;
use Galette\IO\Csv;
use Galette\Filters\MembersList;
use Galette\Entity\FieldsConfig;
use Galette\Entity\Adherent;
use Galette\Repository\Members;

/** @ignore */
require_once 'includes/galette.inc.php';

//Exports main contain user confidential data, they're accessible only for
//admins or staff members
if ( $login->isAdmin() || $login->isStaff() ) {
    $csv = new Csv();

    if ( isset($session['filters']['members'])
        && !isset($_POST['mailing'])
        && !isset($_POST['mailing_new'])
    ) {
        //CAUTION: this one may be simple or advanced, display must change
        $filters = unserialize($session['filters']['members']);
    } else {
        $filters = new MembersList();
    }

    $export_fields = null;
    if ( file_exists(GALETTE_CONFIG_PATH  . 'local_export_fields.inc.php') ) {
        include_once GALETTE_CONFIG_PATH  . 'local_export_fields.inc.php';
        $export_fields = $fields;
    }

    // fields visibility
    $fc = new FieldsConfig(Adherent::TABLE, null);
    $visibles = $fc->getVisibilities();
    $fields = array();
    $headers = array();
    include_once 'includes/members_fields.php';
    foreach ( $members_fields as $k=>$f ) {
        if ( $k !== 'mdp_adh'
            && $export_fields === null
            || (is_array($export_fields) && in_array($k, $export_fields))
        ) {
            if ( $visibles[$k] === FieldsConfig::VISIBLE ) {
                $fields[] = $k;
                $labels[] = $f['label'];
            } else if ( ($login->isAdmin()
                || $login->isStaff()
                || $login->isSuperAdmin())
                && $visibles[$k] === FieldsConfig::ADMIN
            ) {
                $fields[] = $k;
                $labels[] = $f['label'];
            }
        }
    }

    $members = new Members($filters);
    $members_list = $members->getMembersList(
        false,
        $fields,
        true,
        false,
        false,
        true,
        true
    );

    $filename = 'filtered_memberslist.csv';
    $filepath = Csv::DEFAULT_DIRECTORY . $filename;
    $fp = fopen($filepath, 'w');
    if ( $fp ) {
        $res = $csv->export(
            $members_list,
            Csv::DEFAULT_SEPARATOR,
            Csv::DEFAULT_QUOTE,
            $labels,
            $fp
        );
        fclose($fp);
        $written[] = array(
            'name' => $filename,
            'file' => $filepath
        );
    }

    if (file_exists(Csv::DEFAULT_DIRECTORY . $filename) ) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');
        header('Pragma: no-cache');
        readfile(Csv::DEFAULT_DIRECTORY . $filename);
    } else {
        Analog::log(
            'A request has been made to get an exported file named `' .
            $filename .'` that does not exists.',
            Analog::WARNING
        );
        header('HTTP/1.0 404 Not Found');
    }
} else {
    Analog::log(
        'A non authorized person asked to retrieve exported file named `' .
        $filename . '`. Access ha not been granted.',
        Analog::WARNING
    );
    header('HTTP/1.0 403 Forbidden');
}
