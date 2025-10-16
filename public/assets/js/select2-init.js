// Global Select2 initializer
// Applies to select.form-control by default; opt-out with data-no-select2="1"
(function () {
  function normalizeResults(data) {
    // Accepts arrays of {id,text} or objects with {id,nome}
    if (Array.isArray(data)) {
      return data.map(function (o) {
        if (o && typeof o === 'object') {
          return {
            id: o.id ?? o.value ?? '',
            text: o.text ?? o.nome ?? String(o.label ?? o.id ?? ''),
          };
        }
        return { id: o, text: String(o) };
      });
    }
    return [];
  }

  function initSelect2($el) {
    if ($el.data('select2')) return;

    var theme = $el.data('select2-theme') || 'bootstrap4';
    var ajaxUrl = $el.data('ajaxUrl') || $el.attr('data-ajax-url');
    var allowClear = $el.find('option[value=""]').length > 0;
    var placeholder;
    if (ajaxUrl) {
      placeholder = $el.attr('placeholder') || 'Digite para buscar…';
    } else {
      placeholder = $el.attr('placeholder') || ($el.find('option[value=""]').first().text()) || 'Selecione';
    }
    var lang = {
      errorLoading: function () { return 'Os resultados não puderam ser carregados.'; },
      inputTooLong: function (args) {
        var over = args.input.length - args.maximum;
        return 'Apague ' + over + ' caractere' + (over === 1 ? '' : 's');
      },
      inputTooShort: function (args) {
        var remaining = args.minimum - args.input.length;
        return 'Digite ' + remaining + ' caractere' + (remaining === 1 ? '' : 's');
      },
      loadingMore: function () { return 'Carregando mais resultados…'; },
      maximumSelected: function (args) { return 'Você só pode selecionar ' + args.maximum + ' item' + (args.maximum === 1 ? '' : 's'); },
      noResults: function () { return 'Nenhum resultado encontrado'; },
      searching: function () { return 'Pesquisando…'; },
      removeAllItems: function () { return 'Remover todos os itens'; }
    };

    var opts = {
      theme: theme,
      width: '100%',
      placeholder: placeholder,
      allowClear: allowClear,
      language: lang,
    };

    // Ensure dropdown renders within a sensible parent to avoid layout glitches
    var $dp = $el.closest('td');
    if (!$dp.length) $dp = $el.closest('.form-group');
    if (!$dp.length) $dp = $el.parent();
    if ($dp.length) {
      opts.dropdownParent = $dp;
    }

    if (ajaxUrl) {
      opts.ajax = {
        url: ajaxUrl,
        dataType: 'json',
        delay: 250,
        data: function (params) {
          return { q: params.term, page: params.page || 1 };
        },
        processResults: function (data) {
          var items = normalizeResults(data.results || data);
          return { results: items };
        },
        cache: true,
      };
      opts.minimumInputLength = 1;
    }

    $el.select2(opts);

    // Ajusta placeholder do campo de busca quando abrir (para AJAX)
    if (ajaxUrl) {
      $el.on('select2:open', function () {
        var search = document.querySelector('.select2-container--open .select2-search__field');
        if (search) search.setAttribute('placeholder', 'Digite para buscar…');
      });
    }
  }

  // Expose helpers so dynamic content can reuse the same setup
  window.AppSelect2 = window.AppSelect2 || {};
  window.AppSelect2.init = function (el) {
    var $el = el instanceof jQuery ? el : $(el);
    if (!$el || !$el.length) return;
    $el.each(function () {
      initSelect2($(this));
    });
  };
  window.AppSelect2.initAll = function (scope) {
    var $scope;
    if (!scope) {
      $scope = $(document);
    } else if (scope instanceof jQuery) {
      $scope = scope;
    } else {
      $scope = $(scope);
    }
    if (!$scope || !$scope.length) return;
    $scope.find('select.form-control.select2').each(function () {
      initSelect2($(this));
    });
  };

  $(function () {
    // Initialize only when class="select2" is present
    $('select.form-control.select2').each(function () {
      initSelect2($(this));
    });

    // handle dynamically added selects (e.g., in produto edit)
    $(document).on('focus', 'select.form-control.select2', function () {
      var $t = $(this);
      if ($t.data('select2')) return;
      initSelect2($t);
    });
  });
})();
