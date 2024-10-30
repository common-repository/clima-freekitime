<?php
/* 
Plugin Name: clima freekitime 
Plugin URI: http://clima.freekitime.com
Description: selecciona la ciudad y muestra las condiciones climaticas de la misma usando la api de yahoo! weather.  
Version: 1.0 
Author: Ing. Jose Manuel Santibañez Villanueva
Author URI: http://freekitime.com/autor
License: GPL2
*/  

/*  Copyright 2012 Ing. Jose Manuel Santibañez Villanueva  (email : jmsv)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

require("xml2array.php");

//incluir archivo de widget
require("widget-clima-freekitime.php");

add_action('init', 'widget_clima_freekitime_init', 1);

//incluir hoja de estilo

add_action( 'wp_enqueue_scripts', 'clima_freekitime_css' );

    /**
     * Enqueue plugin style-file
     */
    function clima_freekitime_css() {
        // Respects SSL, Style.css is relative to the current file
        wp_register_style( 'clima_freekitime_hoja1', plugins_url('estilo_clima_freekitime.css', __FILE__) );
        wp_enqueue_style( 'clima_freekitime_hoja1' );
    }

//traducion del estado del tiempo
function get_estado_tiempo_freekitime($cod1)
{
	$gt = file_get_contents(plugins_url( 'tiempo.xml' , __FILE__ ));
	$weather = xml2array($gt);
	return $weather['clima']['tiempo'][$cod1];
}

//retorna el codigo a mostrar
function get_clima_freekitime($ciudad)
{
	$contents = file_get_contents('http://weather.yahooapis.com/forecastrss?p='.$ciudad.'&u=c');
	$result = xml2array($contents);
	
	//ciudad
	$ciudad = $result['rss']['channel']['yweather:location_attr']['city'];
	//pais
	$pais = $result['rss']['channel']['yweather:location_attr']['country'];
	//codigo de clima
	$cod = $result['rss']['channel']['item']['yweather:condition_attr']['code'];
	//temperatura
	$temperatura = $result['rss']['channel']['item']['yweather:condition_attr']['temp'];
	//temperatura minima hoy
	$tlh = $result['rss']['channel']['item']['yweather:forecast']['0_attr']['low'];
	//temperatura maxima hoy
	$thh = $result['rss']['channel']['item']['yweather:forecast']['0_attr']['high'];
	//hora de alba
	$alba = $result['rss']['channel']['yweather:astronomy_attr']['sunrise'];
	//hora de ocaso
	$ocaso = $result['rss']['channel']['yweather:astronomy_attr']['sunset'];
	//traduccion de tiempo
	$tiempo = get_estado_tiempo_freekitime($cod);
	//link
	$link = $result['rss']['channel']['item']['link']; 
	//condiciones mañana
	$cod2 = $result['rss']['channel']['item']['yweather:forecast']['1_attr']['code'];
	//estado mañana
	$tiempo2 = get_estado_tiempo_freekitime($cod2);
	//baja mañana
	$tlm = $result['rss']['channel']['item']['yweather:forecast']['1_attr']['low'];
	//alta mañana
	$thm = $result['rss']['channel']['item']['yweather:forecast']['1_attr']['high'];
	//imprimir resultados
	$result = '<table class="clima_freeki" style="width: '.get_option('freekitime_clima_ancho').'px;"><thead><tr class="oddt"><th colspan="2">Clima</th><th colspan="2">'.$ciudad.', '.$pais.'</th></tr></thead><tbody><tr><td colspan="4" class="free_center">'.date('d-M-h:i:s',current_time('timestamp')).'</td><tr /><tr class="odd"><td colspan="4" class="free_center"><img src="http://l.yimg.com/a/i/us/we/52/'.$cod.'.gif" /></td></tr>';
	$result .= '<tr><td colspan="4" class="free_center">'.$tiempo.'</td></tr>';
	$result .= '<tr class="odd"><td colspan="2">Actual:</td><td colspan="2">'.$temperatura.' &ordm;C</td></tr>';
	$result .= '<tr><td>Min:</td><td>'.$tlh.' &ordm;C</td><td>Max:</td><td>'.$thh.' &ordm;C</td></tr>';
	$result .= '<tr class="odd"><td><img src="'.plugins_url( 'img/dia.png' , __FILE__ ).'" /></td><td>'.$alba.'</td><td>';
	$result .= '   <img src="'.plugins_url( 'img/noche.png' , __FILE__ ).'" /></td><td>'.$ocaso.'</td></tr>';
	if(get_option('freekitime_clima_pronostico') == 'si'){
	$result .= '<tr><th colspan="4" class="free_center">Mañana</th></tr><tr class="odd"><td colspan="4" class="free_center"><img src="http://l.yimg.com/a/i/us/we/52/'.$cod2.'.gif" /></td></tr>';
	$result .= '<tr><td colspan="4" class="free_center">'.$tiempo2.'</td></tr>';
	$result .= '<tr class="odd"><td>Min:</td><td>'.$tlm.' &ordm;C</td><td>Max:</td><td>'.$thm.' &ordm;C</td></tr>';
	}
	$result .= '<tr><td colspan="4" TARGET="_blank"><a href="'.$link.'">Yahoo! weather</a></td></tr>';
	$result .= '</tbody></table>';

return $result;
}


//funcion principal
function clima_freekitime()
{
	echo get_clima_freekitime(get_option('freekitime_clima_codigo_ciudad'));
}


//crear un shortcode
add_shortcode('clima_freekitime','clima_freekitime');


/*creando la pagina administrativa*/
// crea el menu de opciones
add_action('admin_menu', 'freekitime_clima_create_menu');

function freekitime_clima_create_menu() {

	//crea un top-level menu
	add_menu_page('Freekitime clima Plugin Settings', 'Freekitime clima Settings', 'administrator', __FILE__, 'freekitime_clima_settings_page',plugins_url('img/icono.gif', __FILE__));

	//call register settings function
	add_action( 'admin_init', 'freekitime_clima_register_mysettings' );
}


function freekitime_clima_register_mysettings() {
	//register our settings
	register_setting( 'freekitime-clima-settings-group', 'freekitime_clima_codigo_ciudad' );
	register_setting( 'freekitime-clima-settings-group', 'freekitime_clima_ancho' );
	register_setting( 'freekitime-clima-settings-group', 'freekitime_clima_pronostico' );
}

function freekitime_clima_settings_page() {
?>
<div class="wrap">
<h2>Clima Freekitime</h2>

<form method="post" action="options.php">
    <?php settings_fields( 'freekitime-clima-settings-group' ); ?>
    <?php do_settings_fields( 'freekitime-clima-settings-group' ); ?>
    <table class="form-table">
	<tr><th><a href="http://clima.freekitime.com/busca_codigo.html" target="_blank">Busca el codigo de ciudad</a></th></tr>
        <tr valign="top">
        <th scope="row">Codigo de Ciudad:</th>
        <td><input type="text" name="freekitime_clima_codigo_ciudad" value="<?php echo get_option('freekitime_clima_codigo_ciudad'); ?>" /></td>
        </tr>
	<tr valign="top">
        <th scope="row">ancho:</th>
        <td><input type="text" name="freekitime_clima_ancho" value="<?php echo get_option('freekitime_clima_ancho'); ?>" /></td>
        </tr>
	<tr>
	<th scope="row">Pronostico de mañana:</th>
	<td><input type=RADIO name="freekitime_clima_pronostico" value="si" <?php if(get_option('freekitime_clima_pronostico') == 'si'){echo 'CHECKED';} ?> />Si
	<input type=RADIO name="freekitime_clima_pronostico" value="no" <?php if(get_option('freekitime_clima_pronostico') == 'no'){echo 'CHECKED';} ?> />No</td>
	</tr>
    </table>
    
    <p class="submit">
    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
    </p>

</form>
</div>

<?php } ?>
