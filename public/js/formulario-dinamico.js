document.addEventListener('DOMContentLoaded', () => {
    const formCotaManual = document.getElementById('formCotaManual');
    if (!formCotaManual) return; // Sai se o formulário não estiver na página

    const fonteSelect = document.getElementById('fonte');
    const campoLink = document.getElementById('link_evidencia');
    const labelLink = document.querySelector('label[for="link_evidencia"]');
    const campoCnpj = document.getElementById('fornecedor_cnpj');
    const labelCnpj = document.querySelector('label[for="fornecedor_cnpj"]');
    const campoData = document.getElementById('data_coleta');
    const labelData = document.querySelector('label[for="data_coleta"]');

    const atualizarFormulario = () => {
        const fonteSelecionada = fonteSelect.value;

        // Reseta todos os destaques e requisitos
        campoLink.required = false;
        labelLink.innerHTML = 'Link da Evidência';
        labelData.innerHTML = 'Data da Coleta';
        labelCnpj.innerHTML = 'CNPJ do Fornecedor';
        document.querySelectorAll('#formCotaManual .mb-3').forEach(el => el.classList.remove('border-primary', 'p-2', 'rounded'));

        // Aplica lógica baseada na fonte
        switch (fonteSelecionada) {
            case 'Site Especializado':
                document.getElementById('group-link').classList.add('border-primary', 'p-2', 'rounded');
                labelLink.innerHTML = '<strong>Link da Evidência (Obrigatório)</strong>';
                campoLink.required = true;
                break;

            case 'Nota Fiscal':
                document.getElementById('group-fornecedor-cnpj').classList.add('border-primary', 'p-2', 'rounded');
                document.getElementById('group-data').classList.add('border-primary', 'p-2', 'rounded');
                labelData.innerHTML = '<strong>Data da Nota Fiscal (Obrigatório)</strong>';
                labelCnpj.innerHTML = '<strong>CNPJ do Fornecedor (Obrigatório)</strong>';
                break;
            
            // Você pode adicionar outros casos aqui no futuro
            // case 'Pesquisa com Fornecedor': ...
        }
    };

    // Adiciona o listener para o evento de mudança
    fonteSelect.addEventListener('change', atualizarFormulario);

    // Chama a função uma vez no carregamento da página para definir o estado inicial
    atualizarFormulario();
});