document.addEventListener('DOMContentLoaded', () => {

    // Função auxiliar para aplicar a máscara a um elemento, se ele existir
    const aplicarMascara = (seletor, opcoes) => {
        const elemento = document.querySelector(seletor);
        if (elemento) {
            IMask(elemento, opcoes);
        }
    };

    // Aplica as máscaras nos campos específicos de cada formulário
    
    // Formulário de Cadastro de Fornecedor (interno)
    aplicarMascara('#cnpj', { mask: '00.000.000/0000-00' });
    aplicarMascara('#telefone', { 
        mask: [
            { mask: '(00) 0000-0000' },
            { mask: '(00) 00000-0000' }
        ]
    });

    // Formulário de Cotação (público)
    aplicarMascara('#form_cnpj', { mask: '00.000.000/0000-00' });
    aplicarMascara('#form_telefone', { 
        mask: [
            { mask: '(00) 0000-0000' },
            { mask: '(00) 00000-0000' }
        ]
    });
    aplicarMascara('#form_responsavel_cpf', { mask: '000.000.000-00' });

});