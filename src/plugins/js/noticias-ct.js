jQuery(document).ready(function($) {
    $('.selectit input[type="checkbox"]').each(function() {
        var checkbox = $(this);
        var radio = $('<input type="radio">').attr({
            name: checkbox.attr('name'),
            value: checkbox.val(),
            checked: checkbox.prop('checked')
        });
        checkbox.replaceWith(radio);
    });
});

jQuery(document).ready(function($) {
    // Manipular a alteração do checkbox
    $(document).on('change', '.materia-destaque-checkbox', function() {
        var post_id = $(this).data('post-id');
        var is_destaque = $(this).is(':checked') ? '1' : '0';

        // Enviar o status atualizado via AJAX para a função PHP
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'alterar_materias_destaque',
                post_data: [{
                    post_id: post_id,
                    is_destaque: is_destaque
                }]
            },
            beforeSend: function() {
                // Exibir um indicador de carregamento
                $(this).parent().append('<span class="materia-destaque-loading">Aguarde...</span>');
            },
            success: function() {
                // Remover o indicador de carregamento após a alteração bem-sucedida
                $('.materia-destaque-loading').remove();
            },
            error: function() {
                // Exibir uma mensagem de erro em caso de falha
                alert('Ocorreu um erro ao alterar o destaque da matéria. Por favor, tente novamente.');
            }
        });
    });
});

jQuery(document).ready(function($) {
    // Manipular a alteração do checkbox
    $(document).on('change', '.materia-destaque-checkbox', function() {
        var checkboxes = $('.materia-destaque-checkbox');
        var selectedCheckboxes = checkboxes.filter(':checked');

        if (selectedCheckboxes.length >= 3) {
            checkboxes.not(':checked').prop('disabled', true);
        } else {
            checkboxes.prop('disabled', false);
        }

        var mensagemLimite = $('#mensagem-limite-destaque');
        if (selectedCheckboxes.length >= 3) {
            mensagemLimite.text('Você atingiu o limite máximo de matérias em destaque. Não é possível salvar mais de 3 matérias em destaque.');
            mensagemLimite.show();
        } else {
            mensagemLimite.hide();
        }

    });
});
