<?php
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

function add_youtube_field_to_custom_post_type() {
    register_post_meta('noticias-climatempo', 'youtube', array(
        'show_in_rest' => true,
        'single'       => true,
        'type'         => 'string',
    ));
}
add_action('init', 'add_youtube_field_to_custom_post_type');