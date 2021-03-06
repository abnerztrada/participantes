<?php
namespace tool_participantes\models;

// require(dirname(dirname(__FILE__)).'/config.php');
require_once($CFG->libdir.'/clilib.php');
require_once($CFG->libdir.'/moodlelib.php');

/**
 *
 */
class correo
{

  public function __construct()
  {
    // code...
  }

  public function correo_envio(){
      global $DB;

      //Querys de fechas y course
      $query = "Select @s:=@s + 1 id_au, c.id, c.shortname, DATE_FORMAT(DATE_ADD(FROM_UNIXTIME(c.startdate, '%Y-%m-%d %H:%i'), INTERVAL -5 HOUR),'%d/%m/%Y %H:%i') AS fecha,
      DATE_FORMAT(DATE_ADD(FROM_UNIXTIME(c.startdate, '%Y-%m-%d %H:%i'), INTERVAL -5 HOUR),'%d/%m/%Y %H:%i') AS fechainicio,
      DATE_FORMAT(DATE_ADD(FROM_UNIXTIME(c.startdate, '%Y-%m-%d'), INTERVAL -5 HOUR),'%d/%m/%Y') AS fechainicioc,
      DATE_FORMAT(DATE_ADD(FROM_UNIXTIME(c.enddate, '%Y-%m-%d'), INTERVAL -5 HOUR),'%d/%m/%Y') AS fechafinc,
      DATE_FORMAT(DATE_ADD(FROM_UNIXTIME(cd.value, '%Y-%m-%d %H:%i'), INTERVAL -5 HOUR),'%d/%m/%Y %H:%i') AS fecha_avance
      FROM (select @s:=0) as s, mdl_course c
      INNER JOIN mdl_customfield_data cd ON cd.instanceid = c.id
      where c.visible = 1 and cd.fieldid = 37"; /*IN (38,39,40,41,42)*/
      $result = $DB->get_records_sql($query);

      $url = 'http://54.161.158.96/local/rep/report.php?id=';

      

      foreach ($result as $it) {
        // Url con id
        $urltemp = $url.$it->id;
        //fecha avance 
        $fechaavance = $it->fecha_avance; 

        //Fecha inicio
        $fechainicio = $it->fechainicio; 
        $fechainicioc = $it->fechainicioc; 

        //Fecha fin
        $fechafinc = $it->fechafinc; 

        //Query que valida los correos de los stackholder
        $query2 = "SELECT  @s:=@s + 1 id_auto, c.id, concat(u.firstname,' ', u.lastname) as nombre, u.email, c.shortname, c.fullname,
                  asg.roleid, asg.userid, r.shortname as stakholder FROM
                  (select @s:=0) as s,
                  mdl_user u
                  INNER JOIN mdl_role_assignments as asg on asg.userid = u.id
                  INNER JOIN mdl_context as con on asg.contextid = con.id
                  INNER JOIN mdl_course c on con.instanceid = c.id
                  INNER JOIN mdl_role r on asg.roleid = r.id
                  where c.shortname = '$it->shortname' and r.shortname = 'stakeholder'";
        $result2 = $DB->get_records_sql($query2);

        echo '<pre>';
          print_r($result2);
        echo '</pre>';
         

        foreach ($result2 as $it2) {
          $body = $urltemp;
          $emailuser->email = $it2->email;
          $emailuser->id = -99;
          $emailuser->maildisplay = true;
          $emailuser->mailformat = 1;
          $nombre = $it2->nombre;
          $subject = $it2->fullname;

          date_default_timezone_set("America/Guatemala");
          $fechaAct = date("d/m/Y H:i"); // H:i Hora y minuto
          
          //Imagen para el banner
          $String ="<img src='http://54.161.158.96/local/img/img.png'"; 
       
          //Texto de Listado de inscripcion al curso
          $string2 = ""; 
          $string2 .= $String;
          $string2 .= "<div style='color: orange; font-size: 18px; font-family: Century Gothic;'> $nombre </div>";
          $string2 .= "<br>"; 
          $string2 .= "<div style= 'color: black; font-size: 16px; font-family: Century Gothic;'> En el siguiente enlace $body encontrar??s la lista de participantes inscritos al curso: <span style= 'color: orange; font-size: 16px; font-family: Century Gothic;'>$subject.</span> </div>";
          $string2 .= "<div style='color: black; font-size: 16px; font-family: Century Gothic;'> El curso estar?? habilitado del <span style= 'color: orange; font-size: 16px; font-family: Century Gothic;'> $fechainicioc </span> al <span style= 'color: orange; font-size: 16px; font-family: Century Gothic;'> $fechafinc. </span> </div>";
          $string2 .= "<div style='color: black; font-size: 16px; font-family: Century Gothic;'> Cualquier duda o comentario puedes escribirnos a cmi-laucmi@somoscmi.com \n </div>";
          $string2 .= "<br>"; 
          $string2 .= "<div style='color: black; font-size: 16px; font-family: Century Gothic;'> Atentamente, \n </div>";
          $string2 .= "<div style='color: black; font-size: 16px; font-family: Century Gothic;'> laUcmi \n </div>";

          //Comparaciones de fechas para el envio del correo electronico
         if($fechaAct == $fechaavance){
              $email = email_to_user($emailuser,'laUcmi','Listado de inscripci??n al curso '.$subject, $string2);
              echo "Correo enviado";
          }else{
              echo "Correo no enviado";
          } 
        }
      }
    }
  }
?>

