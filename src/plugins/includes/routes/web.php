<?php

function noticias_climatempo_endpoint( $request ) {
    $args = array(
        'post_type'      => 'noticias-climatempo',
        'posts_per_page' => -1,
    );

    $query = new WP_Query( $args );

    $data = array();

    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();

            $post_id = get_the_ID();
            $post_title = get_the_title();
            $post_content = get_the_content();
            $post_thumbnail = get_the_post_thumbnail_url();
            $categories = wp_get_post_categories( $post_id ); // Obtém as categorias associadas ao post
            $youtube = get_post_meta( $post_id, 'youtube', true );
            $materia_destaque = get_post_meta($post_id, 'materia_destaque', true);

            $category_names = array(); // Array para armazenar os nomes das categorias

            foreach ( $categories as $category_id ) {
                $category = get_category( $category_id );
                $category_names[] = $category->name;
            }

            $data[] = array(
                'id'                    => $post_id,
                'title'                 => $post_title,
                'content'               => $post_content,
                'thumbnail'             => $post_thumbnail,
                'categories'            => $category_names, // Adiciona as categorias ao array de dados
                'youtube'               => $youtube,
                'materia_destaque'      => $materia_destaque,
            );
        }
    }

    wp_reset_postdata();

    return rest_ensure_response( $data );
}


// Função para retornar todas as categorias
function noticias_climatempo_categories_endpoint() {
    $categories = get_categories();

    $data = array();
    foreach ($categories as $category) {
    $data[] = array(
        'id' => $category->term_id,
        'name' => $category->name,
        'slug' => $category->slug,
    );
}

return rest_ensure_response($data);
}


//GET ID
function noticias_climatempo_single_endpoint( $request ) {
    $id = $request->get_param( 'id' );
    $post = get_post( $id );

    if ( empty( $post ) || $post->post_type !== 'noticias-climatempo' ) {
        return new WP_Error( 'not_found', 'Notícia Climatempo não encontrada.', array( 'status' => 404 ) );
    }

    $post_id = $post->ID;
    $post_title = get_the_title( $post_id );
    $post_content = get_the_content( $post_id );
    $post_thumbnail = get_the_post_thumbnail_url( $post_id );
    $categories = wp_get_post_categories( $post_id );
    $youtube = get_post_meta( $post_id, 'youtube', true );
    $materia_destaque = get_post_meta($post_id, 'materia_destaque', true);


    $category_names = array();
    foreach ( $categories as $category_id ) {
        $category = get_category( $category_id );
        $category_names[] = $category->name;
    }

    $data = array(
        'id'                => $post_id,
        'title'             => $post_title,
        'content'           => $post_content,
        'thumbnail'         => $post_thumbnail,
        'categories'        => $category_names,
        'youtube'           => $youtube,
        'materia_destaque'  => $materia_destaque,
    );

    return rest_ensure_response( $data );
}


function listar_posts_por_categoria($request) {
    $categoria_slug = $request['categoria'];

    $args = array(
        'post_type' => 'noticias-climatempo',
        'tax_query' => array(
            array(
                'taxonomy' => 'category',
                'field' => 'slug',
                'terms' => $categoria_slug,
            ),
        ),
    );

    $query = new WP_Query($args);

    $data = array();

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();

            $post_id = get_the_ID();
            $post_title = get_the_title();
            $post_content = get_the_content();
            $post_thumbnail = get_the_post_thumbnail_url();
            $categories = wp_get_post_categories($post_id);
            $youtube = get_post_meta($post_id, 'youtube', true);
            $materia_destaque = get_post_meta($post_id, 'materia_destaque', true);

            $category_names = array();
            foreach ($categories as $category_id) {
                $category = get_category($category_id);
                $category_names[] = $category->name;
            }

            $data[] = array(
                'id' => $post_id,
                'title' => $post_title,
                'content' => $post_content,
                'thumbnail' => $post_thumbnail,
                'categories' => $category_names,
                'youtube' => $youtube,
                'materia_destaque' => $materia_destaque,
            );
        }
    }

    wp_reset_postdata();

    return rest_ensure_response($data);
}

function register_noticias_climatempo_endpoint() {
    register_rest_route('wp/v2', '/noticias-climatempo', array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'noticias_climatempo_endpoint',
    ));

    register_rest_route('wp/v2', '/noticias-climatempo/(?P<id>\d+)', array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'noticias_climatempo_single_endpoint',
    ));

    register_rest_route('wp/v2', '/noticias-climatempo/categorias', array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'noticias_climatempo_categories_endpoint',
    ));

    register_rest_route('wp/v2', '/noticias-climatempo/(?P<categoria>[\w-]+)', array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'listar_posts_por_categoria',
    ));
}
add_action('rest_api_init', 'register_noticias_climatempo_endpoint');