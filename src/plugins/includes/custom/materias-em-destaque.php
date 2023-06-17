<?php

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