<?php
/*
Plugin Name: Notícias Climatempo Plugin
Description: Plugin para adicionar o custom post type Notícias Climatempo e seus campos personalizados.
Version: 1.0
Author: Adolpho Cavalcanti
*/


// Verificar se está havendo algum acesso direto ao arquivo do plugin que não esteja autorizado
if(!defined('ABSPATH')){
      die;
}

require_once(plugin_dir_path(__FILE__) . 'includes/custom/materias-em-destaque.php');
require_once(plugin_dir_path(__FILE__) . 'includes/custom/custom-field.php');
require_once(plugin_dir_path(__FILE__) . 'includes/routes/web.php');
require_once(plugin_dir_path(__FILE__) . 'includes/model/noticias-climatempo-custom.php');

//Importando JS externo
// Adicionar a função de enfileiramento de scripts ao gancho 'wp_enqueue_scripts'
add_action(
    'wp_enqueue_scripts',
    wp_enqueue_script(
        'meu-plugin-clima-script', 
        plugin_dir_url(__FILE__) . 'js/noticias-ct.js', 
        array('jquery'), 
        '1.0.0', 
        true
    )
);

// Registrar e enfileirar o arquivo CSS
// Adicionar a função de enfileiramento de estilos ao gancho 'wp_enqueue_scripts'
add_action(
    'wp_enqueue_scripts', 
    wp_enqueue_style('meu-plugin-style', plugin_dir_url(__FILE__) . 'css/noticias-ct.css')
);