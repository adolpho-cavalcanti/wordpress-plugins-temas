<?php

// Criação do Custom Post Type
function criar_custom_post_type() {
    $labels = array(
        'name'               => 'Notícias Climatempo',
        'singular_name'      => 'Notícia Climatempo',
        'menu_name'          => 'Notícias Climatempo',
        'name_admin_bar'     => 'Notícias Climatempo',
        'add_new'            => 'Adicionar Nova',
        'add_new_item'       => 'Adicionar Nova Notícia Climatempo',
        'new_item'           => 'Nova Notícia Climatempo',
        'edit_item'          => 'Editar Notícia Climatempo',
        'view_item'          => 'Visualizar Notícia Climatempo',
        'all_items'          => 'Todas as Notícias Climatempo',
        'search_items'       => 'Pesquisar Notícias Climatempo',
        'parent_item_colon'  => 'Notícias Climatempo Pai:',
        'not_found'          => 'Nenhuma Notícia Climatempo encontrada.',
        'not_found_in_trash' => 'Nenhuma Notícia Climatempo encontrada na lixeira.'
    );

    $args = array(
        'labels'              => $labels,
        'public'              => true,
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'query_var'           => true,
        'rewrite'             => array( 'slug' => 'noticias-climatempo' ),
        'capability_type'     => 'post',
        'has_archive'         => true,
        'hierarchical'        => false,
        'menu_position'       => null,
        'supports'            => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
        'taxonomies'          => array( 'category' ),
    );

    register_post_type( 'noticias-climatempo', $args );
}
add_action( 'init', 'criar_custom_post_type' );