<?php

add_theme_support('post-thumbnails');


// Adicione a função para criar a meta box do campo "title-ads"
function adicionar_meta_box_title_ads() {
    add_meta_box('meta_box_title_ads_id', 'Título dos Anúncios', 'exibir_meta_box_title_ads', 'page', 'normal', 'high');
}
add_action('add_meta_boxes', 'adicionar_meta_box_title_ads');

// Função para exibir a meta box do campo "title-ads"
function exibir_meta_box_title_ads($post) {
    $valor_title_ads = get_post_meta($post->ID, 'title-ads', true);
    ?>
    <label for="title-ads">Título dos Anúncios:</label>
    <input type="text" id="title-ads" name="title-ads" value="<?php echo esc_attr($valor_title_ads); ?>" />
    <?php
}

// Salve o valor do campo "title-ads"
function salvar_campos_personalizados_title_ads($post_id) {
    if (array_key_exists('title-ads', $_POST)) {
        update_post_meta($post_id, 'title-ads', sanitize_text_field($_POST['title-ads']));
    }
}
add_action('save_post', 'salvar_campos_personalizados_title_ads');

// Adicione a função para criar a meta box do campo "meta-title-ads"
function adicionar_meta_box_meta_title_ads() {
    add_meta_box('meta_box_meta_title_ads_id', 'Meta Título dos Anúncios', 'exibir_meta_box_meta_title_ads', 'page', 'normal', 'high');
}
add_action('add_meta_boxes', 'adicionar_meta_box_meta_title_ads');

// Função para exibir a meta box do campo "meta-title-ads"
function exibir_meta_box_meta_title_ads($post) {
    $valor_meta_title_ads = get_post_meta($post->ID, 'meta-title-ads', true);
    ?>
    <label for="meta-title-ads">Meta Título dos Anúncios:</label>
    <input type="text" id="meta-title-ads" name="meta-title-ads" value="<?php echo esc_attr($valor_meta_title_ads); ?>" />
    <?php
}

// Salve o valor do campo "meta-title-ads"
function salvar_campos_personalizados_meta_title_ads($post_id) {
    if (array_key_exists('meta-title-ads', $_POST)) {
        update_post_meta($post_id, 'meta-title-ads', sanitize_text_field($_POST['meta-title-ads']));
    }
}
add_action('save_post', 'salvar_campos_personalizados_meta_title_ads');

add_action('rest_api_init', 'adicionar_campos_personalizados_api');

function adicionar_campos_personalizados_api() {
    register_rest_field('page', 'title_ads', array(
        'get_callback' => 'obter_valor_title_ads',
        'update_callback' => null,
        'schema' => null,
    ));

    register_rest_field('page', 'meta_title_ads', array(
        'get_callback' => 'obter_valor_meta_title_ads',
        'update_callback' => null,
        'schema' => null,
    ));
}

function obter_valor_title_ads($object, $field_name, $request) {
    return get_post_meta($object['id'], 'title-ads', true);
}

function obter_valor_meta_title_ads($object, $field_name, $request) {
    return get_post_meta($object['id'], 'meta-title-ads', true);
}

