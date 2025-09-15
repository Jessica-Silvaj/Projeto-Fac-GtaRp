<script type="text/javascript" src="{{ asset('assets/js/jquery/jquery.min.js') }} "></script>
<script type="text/javascript" src="{{ asset('assets/js/jquery-ui/jquery-ui.min.js ') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/popper.js/popper.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/bootstrap/js/bootstrap.min.js') }} "></script>
<script src="{{ asset('assets/pages/waves/js/waves.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/jquery-slimscroll/jquery.slimscroll.js') }}"></script>
<script src="{{ asset('assets/js/jquery.mCustomScrollbar.concat.min.js') }} "></script>
<script src="{{ asset('assets/js/pcoded.min.js') }}"></script>
<script src="{{ asset('assets/js/vertical/vertical-layout.min.js ') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/script.js') }} "></script>
<script type="text/javascript" src="{{ asset('assets/js/jquery.mask/jquery.mask.js') }}"></script>
<script src="{{ asset('assets/js/mc-calendar/mc-calendar.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/jquery-validate/jquery.validate.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>



@yield('script')

<script>
    $('.data-mask').each(function() {
        $('[name="' + $(this).attr('name') + '"]').attr('id', $(this).attr('name'));

        var formato = $('[name="' + $(this).attr('name') + '"]').attr('data-format');
        MCDatepicker.create({
            el: "#" + $(this).attr('id'),
            dateFormat: formato,
            bodyType: 'inline',
            customOkBTN: 'Ok',
            customClearBTN: 'Limpar',
            customCancelBTN: 'Cancelar',
            closeOndblclick: true,
            closeOnBlur: true,
            autoClose: true,
            customMonths: [
                'Janeiro',
                'Fevereiro',
                'Março',
                'Abril',
                'Maio',
                'Junho',
                'Julho',
                'Agosto',
                'Setembro',
                'Outubro',
                'Novembro',
                'Dezembro'
            ],
            customWeekDays: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado']
        })
    });

    const rules = {
        SenhaAtual: {
            required: true,
        },
        NovaSenha: {
            required: true,
            minlength: 6,
            maxlength: 20
        },
        ConfirmacaoNovaSenha: {
            required: true,
            equalTo: "#NovaSenha"
        },
    };

    const messages = {
        SenhaAtual: {
            required: "Informe a sua senha atual.",
        },
        NovaSenha: {
            required: "Informe a nova senha.",
            minlength: "A senha deve ter no mínimo 6 caracteres",
            maxlength: "A senha deve ter no máximo 20 caracteres"
        },
        ConfirmacaoNovaSenha: {
            required: "Confirme a nova senha.",
            equalTo: "As senhas não são iguais."
        },
    };

    const form = $("form.alterarSenha");
    validarFormulario(form, rules, messages);

    $("#NovaSenha").on('keyup', function() {
        var number = /([0-9])/;
        var alphabets = /([a-zA-Z])/;
        var special_characters = /([~,!,@,#,$,%,^,&,*,-,_,+,=,?,>,<])/;
        if ($('#NovaSenha').val().length < 6) {
            $('#password-strength-status').removeClass();
            $('#password-strength-status').addClass('weak-password');
            $('#password-strength-status').html("Fraca");
        } else {
            if ($('#NovaSenha').val().match(number) && $('#NovaSenha').val().match(alphabets) && $('#NovaSenha')
                .val().match(special_characters)) {
                $('#password-strength-status').removeClass();
                $('#password-strength-status').addClass('strong-password');
                $('#password-strength-status').html("Forte");
            } else {
                $('#password-strength-status').removeClass();
                $('#password-strength-status').addClass('medium-password');
                $('#password-strength-status').html("Média");
            }
        }
    });
</script>
