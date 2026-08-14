<?php
// ============================================================
// controllers/SlopeUpdateController.php  –  Contrôleur Pentes
// ============================================================

namespace controllers;

use models\Slope;
use models\SlopeUpdate;
use function jsonResponse;


require_once __DIR__ . '/../models/Slope.php';
require_once __DIR__ . '/../models/SlopeUpdate.php';
require_once __DIR__ . '/../helpers/response.php';


class SlopeUpdateController
{
    public static function ShowDiff(int $updateId) {
        $update = SlopeUpdate::getById($updateId);
        if ($update)
        {
            $slopeId = $update['slopeId'];
            $slope = Slope::getById($slopeId);
            $serverName = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']
                === 'on' ? "https" : "http") .
                "://" . $_SERVER['HTTP_HOST'];
            $slopeUrl = $serverName . "/" . $slopeId;
            $slopeEditUrl = $serverName . "/admin/table.php?t=slopes&mode=edit&pk=" . $slopeId;

            $vars = array(
                'TABLE' => self::showUpdateTable($updateId),
                'NAME' => $slope['name'],
                'SLOPEID'=> $slopeId,
                'SLOPEURL'=> $slopeUrl,
                'SLOPEEDITURL'=> $slopeEditUrl,
            );
            $body = file_get_contents('./diff.php');
            if(isset($vars)){
                foreach($vars as $k=>$v){
                    $body = str_replace('{'.strtoupper($k).'}', $v, $body);
                }
            }
            echo $body;
        }
        else
        {
            echo "Diff Id non trouvé ! ";
        }

        //include_once('diff.php');
    }

    public static function showUpdateTable(int $updateId): string
    {
        $html  = "<table class='table'>";
        $html .= "<thead class='table-light'>";
        $html .= "    <tr>";
        $html .= "        <th scope='col'>Champ</th><th scope='col'>Valeur originale</th><th scope='col'>Nouvelle valeur</th>";
        $html .= "    </tr>";
        $html .= "</thead>";
        $update = SlopeUpdate::getById($updateId);
        if ($update)
        {
            //var_dump($update);
            $slopeId = $update['slopeId'];
            $text = $update['text'];
            $updateInput = json_decode($text,true);

            $slope = Slope::getById($slopeId);


            foreach ($slope as $key => $value) {
                if (key_exists($key,$slope) and key_exists($key,$updateInput))
                {

                    $original = "";
                    $new = "";
                    if (is_array($value))
                    {
                        $original = implode(",",$value);
                    }
                    else
                    {
                        if (!is_null($value))
                            $original = trim(strip_tags($value));
                        else
                            $original = "";
                    }
                    if (is_array($updateInput[$key]))
                    {
                        $new = implode(',',$updateInput[$key]);
                    }
                    else
                    {
                        if (!is_null($updateInput[$key]))
                            $new = trim(strip_tags($updateInput[$key]));
                        else
                            $new = '';
                    }


                    if ($original == $new)
                    {
                        $html .= "    <tr>";
                        $html .= "        <td>".$key."</td><td>No update</td><td>No update</td>";
                        $html .= "    </tr>";
                    }
                    else
                    {
                        $html .= "    <tr class='table-warning'>";
                        $html .= "        <td>".$key."</td><td>".$original."</td><td>".$new."</td>";
                        $html .= "    </tr>";
                    }

                }
            }
        }
        return $html;
    }

}
