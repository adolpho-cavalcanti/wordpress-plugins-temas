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




//materias-em-destaque.php



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


function add_youtube_field_to_custom_post_type() {
    register_post_meta('noticias-climatempo', 'youtube', array(
        'show_in_rest' => true,
        'single'       => true,
        'type'         => 'string',
    ));
}
add_action('init', 'add_youtube_field_to_custom_post_type');


////////////////////////////////////////////////////////////////////////////////////////////////////////
/**
 * Adicione o campo personalizado ao tipo de post personalizado 
 */

function adicionar_campo_youtube() {
    add_meta_box(
        'youtube',
        'YouTube',
        'exibir_youtube',
        'noticias-climatempo',
        'normal',
        'default'
    );
}
add_action( 'add_meta_boxes', 'adicionar_campo_youtube' );

/**
 * Mostrar o campo personalizado no custom post type
 */
// Exiba o campo personalizado
function exibir_youtube( $post ) {
    // Recupere o valor do campo, se existir
    $valor_campo = get_post_meta( $post->ID, 'youtube', true );
    ?>
        <div class="inputBox" style="display:flex; flex-direction: column; width: 100%;">
            <label for="youtube">Digite o link do YouTube:</label>
            <input type="text" name="youtube" id="youtube" 
                style="margin: 5px 0; width: 100%;" 
                value="<?php echo esc_attr( $valor_campo ); ?>"
            >
        </div>
    <?php
}

// Salve o valor do campo personalizado
function salvar_youtube( $post_id ) {
    if ( isset( $_POST['youtube'] ) ) {
        $valor_campo = sanitize_text_field( $_POST['youtube'] );
        update_post_meta( $post_id, 'youtube', $valor_campo );
    }
}
add_action( 'save_post', 'salvar_youtube' );


// Salvar o valor do campo "materia_destaque" ao atualizar o post
function salvar_metabox_materia_destaque($post_id) {
    // Verificar se o campo foi enviado no formulário
    if (isset($_POST['materia_destaque'])) {
        // Atualizar o valor do campo "materia_destaque" como verdadeiro (true)
        update_post_meta($post_id, 'materia_destaque', 1);
    } else {
        // Caso contrário, atualizar o valor do campo "materia_destaque" como falso (false)
        update_post_meta($post_id, 'materia_destaque', 0);
    }
}
add_action('save_post', 'salvar_metabox_materia_destaque');

/**
 * Add matéira em destaque como COLUNA na listagem de custom posts
 */
// Adicionar coluna "Matéria Destaque" na listagem do custom post type
function adicionar_coluna_materia_destaque($columns) {
    $columns['materia_destaque'] = 'Matéria Destaque';
    return $columns;
}
add_filter('manage_noticias-climatempo_posts_columns', 'adicionar_coluna_materia_destaque');


function exibir_conteudo_coluna_materia_destaque($column, $post_id) {
    if ($column === 'materia_destaque') {
        $materia_destaque = get_post_meta($post_id, 'materia_destaque', true);

        // Obtém a quantidade atual de matérias em destaque
        $materias_destaque_atuais = get_posts(array(
            'post_type' => 'noticias-climatempo',
            'meta_key' => 'materia_destaque',
            'meta_value' => '1',
            'posts_per_page' => -1,
            'fields' => 'ids',
        ));
        $materias_destaque_atuais_count = count($materias_destaque_atuais);

        // Verifica se o número atual de matérias em destaque é igual a 3
        $checkboxes_disabled = ($materias_destaque_atuais_count === 3 && !$materia_destaque) ? 'disabled' : '';

        // Adiciona apenas o checkbox (sem o botão) e verifica se deve ser desabilitado
        echo '<span class="materia-destaque-status">' . ($materia_destaque ? 'Sim' : 'Não') . '</span>';
        echo '<input type="checkbox" class="materia-destaque-checkbox" data-post-id="' . $post_id . '" ' . checked($materia_destaque, '1', false) . ' ' . $checkboxes_disabled . '>';

        // Exibe a mensagem de limite de matérias em destaque
        if ($materias_destaque_atuais_count === 3) {
            echo '<script type="text/javascript">jQuery("#mensagem-limite-destaque").text("Você atingiu o limite máximo de matérias em destaque. Não é possível salvar mais de 3 matérias em destaque.").show();</script>';
        }
    }
}
add_action('manage_noticias-climatempo_posts_custom_column', 'exibir_conteudo_coluna_materia_destaque', 10, 2);


// Adicione um botão "Alterar" acima da tabela de listagem
function adicionar_botao_alterar_materia_destaque($columns) {
    if (isset($_GET['post_type']) && $_GET['post_type'] === 'noticias-climatempo') {
        $columns['materia_destaque'] .= ' <button id="botao-alterar-materia-destaque" class="button-secondary">Alterar</button>';
    }
    return $columns;
}
add_filter('manage_noticias-climatempo_posts_columns', 'adicionar_botao_alterar_materia_destaque');


// Função para alterar o status das matérias em destaque em massa
function alterar_materias_destaque() {
    if (isset($_POST['post_data'])) {
        $post_data = $_POST['post_data'];

        // Obtém a quantidade atual de matérias em destaque
        $materias_destaque_atuais = get_posts(array(
            'post_type' => 'noticias-climatempo',
            'meta_key' => 'materia_destaque',
            'meta_value' => '1',
            'posts_per_page' => -1,
            'fields' => 'ids',
        ));

        $materias_destaque_atuais_count = count($materias_destaque_atuais);

        foreach ($post_data as $item) {
            $post_id = $item['post_id'];
            $is_destaque = $item['is_destaque'];

            // Verifica se está adicionando uma matéria em destaque e se a quantidade atual é menor que 3
            if ($is_destaque === '1' && $materias_destaque_atuais_count < 3) {
                // Atualiza o valor da meta 'materia_destaque' para o post
                update_post_meta($post_id, 'materia_destaque', $is_destaque);
                $materias_destaque_atuais_count++;
            } elseif ($is_destaque === '0') {
                // Remove o destaque da matéria
                delete_post_meta($post_id, 'materia_destaque');
                $materias_destaque_atuais_count--;

            }
        }
        
        wp_die();
    }
}
add_action('wp_ajax_alterar_materias_destaque', 'alterar_materias_destaque');

function adicionar_mensagem_limite_destaque() {
    ?>
    <div id="mensagem-limite-destaque" class="notice notice-warning" style="display: none;"></div>
    <?php
}
add_action('admin_notices', 'adicionar_mensagem_limite_destaque');

//FILTRAR
function adicionar_filtro_materia_destaque() {
    global $typenow;
    
    // Verifica se está na página correta do custom post type
    if ($typenow == 'noticias-climatempo') {
        // Obtém o valor atual do filtro (se existir)
        $materia_destaque_atual = isset($_GET['materia_destaque']) ? $_GET['materia_destaque'] : '';
        
        // Define as opções do filtro
        $opcoes_filtro = array(
            '' => 'Todas as matérias',
            'sim' => 'Materias em destaque',
            'nao' => 'Materias não em destaque'
        );
        
        // Exibe o seletor de filtro
        echo '<select name="materia_destaque">';
        
        // Gera as opções do seletor
        foreach ($opcoes_filtro as $valor => $rotulo) {
            printf(
                '<option value="%s" %s>%s</option>',
                $valor,
                selected($materia_destaque_atual, $valor, false),
                $rotulo
            );
        }
        
        echo '</select>';
    }
}
add_action('restrict_manage_posts', 'adicionar_filtro_materia_destaque');

function filtrar_por_materia_destaque($query) {
    global $pagenow, $typenow;
    
    // Verifica se está na página correta do custom post type e se há uma opção de filtro selecionada
    if ($pagenow == 'edit.php' && $typenow == 'noticias-climatempo' && isset($_GET['materia_destaque']) && $_GET['materia_destaque'] !== '') {
        $meta_key = 'materia_destaque';
        $meta_value = $_GET['materia_destaque'] === 'sim' ? '1' : '0';
        
        // Adiciona o meta query para filtrar os posts
        $query->query_vars['meta_key'] = $meta_key;
        $query->query_vars['meta_value'] = $meta_value;
    }
}
add_filter('pre_get_posts', 'filtrar_por_materia_destaque');