// Movimentações JavaScript - Listagem com filtros e paginação

let paginaAtual = 1;
const limitePorPagina = 20;
let produtosCache = [];

document.addEventListener('DOMContentLoaded', () => {
    // Definir datas padrão (últimos 30 dias)
    const hoje = new Date();
    const trintaDiasAtras = new Date();
    trintaDiasAtras.setDate(hoje.getDate() - 30);
    
    document.getElementById('data_fim').value = formatarDataInput(hoje);
    document.getElementById('data_inicio').value = formatarDataInput(trintaDiasAtras);
    
    carregarProdutosParaFiltro();
    carregarMovimentacoes();
    
    document.getElementById('btnLogout').addEventListener('click', logout);
    document.getElementById('formFiltros').addEventListener('submit', (e) => {
        e.preventDefault();
        paginaAtual = 1;
        carregarMovimentacoes();
    });
});

async function carregarProdutosParaFiltro() {
    try {
        const response = await apiFetch('produtos/listar.php?ativo=1');
        const data = await apiJson(response);
        if (data.sucesso) {
            produtosCache = data.dados;
            const select = document.getElementById('produto_id');
            select.innerHTML = '<option value="">Todos</option>' + 
                produtosCache.map(p => `<option value="${p.id}">${p.nome}</option>`).join('');
        }
    } catch (e) {
        console.error('Erro ao carregar produtos:', e);
    }
}

async function carregarMovimentacoes() {
    const formData = new FormData(document.getElementById('formFiltros'));
    const params = new URLSearchParams();
    
    for (const [key, value] of formData.entries()) {
        if (value) params.append(key, value);
    }
    params.append('pagina', paginaAtual);
    params.append('limite', limitePorPagina);
    
    try {
        const response = await apiFetch(`estoque/movimentacoes.php?${params.toString()}`);
        const data = await apiJson(response);
        
        const tbody = document.querySelector('#tabelaMovimentacoes tbody');
        const paginacao = document.getElementById('paginacao');
        const paginacaoInfo = document.getElementById('paginacaoInfo');
        
        if (!data.sucesso || !data.dados || data.dados.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center text-secondary py-4">Nenhuma movimentação encontrada</td></tr>';
            paginacao.innerHTML = '';
            paginacaoInfo.textContent = '';
            return;
        }
        
        tbody.innerHTML = data.dados.map(mov => `
            <tr>
                <td>${formatarDataHora(mov.data_movimentacao)}</td>
                <td><strong>${mov.produto_nome}</strong></td>
                <td>${mov.numero_lote}</td>
                <td>${formatarData(mov.data_validade)}</td>
                <td><span class="badge ${mov.tipo === 'entrada' ? 'bg-success' : 'bg-danger'}">${mov.tipo}</span></td>
                <td>${mov.quantidade}</td>
                <td>${mov.usuario_nome}</td>
                <td>${mov.observacao || '-'}</td>
            </tr>
        `).join('');
        
        // Paginação
        const { pagina, total_paginas, total } = data.paginacao;
        paginaAtual = pagina;
        
        if (total_paginas > 1) {
            let pagHtml = '';
            
            // Anterior
            pagHtml += `<li class="page-item ${pagina === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="mudarPagina(${pagina - 1}); return false;" aria-label="Anterior"><i class="bi bi-chevron-left"></i></a>
            </li>`;
            
            // Páginas
            let inicio = Math.max(1, pagina - 2);
            let fim = Math.min(total_paginas, inicio + 4);
            if (fim - inicio < 4) {
                inicio = Math.max(1, fim - 4);
            }
            
            for (let i = inicio; i <= fim; i++) {
                pagHtml += `<li class="page-item ${i === pagina ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="mudarPagina(${i}); return false;">${i}</a>
                </li>`;
            }
            
            // Próximo
            pagHtml += `<li class="page-item ${pagina === total_paginas ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="mudarPagina(${pagina + 1}); return false;" aria-label="Próximo"><i class="bi bi-chevron-right"></i></a>
            </li>`;
            
            paginacao.innerHTML = pagHtml;
        } else {
            paginacao.innerHTML = '';
        }
        
        const inicio = (pagina - 1) * limitePorPagina + 1;
        const fim = Math.min(pagina * limitePorPagina, total);
        paginacaoInfo.textContent = `Mostrando ${inicio} a ${fim} de ${total} registros`;
        
    } catch (e) {
        console.error('Erro ao carregar movimentações:', e);
    }
}

function mudarPagina(pagina) {
    paginaAtual = pagina;
    carregarMovimentacoes();
}

function limparFiltros() {
    document.getElementById('formFiltros').reset();
    const hoje = new Date();
    const trintaDiasAtras = new Date();
    trintaDiasAtras.setDate(hoje.getDate() - 30);
    document.getElementById('data_fim').value = formatarDataInput(hoje);
    document.getElementById('data_inicio').value = formatarDataInput(trintaDiasAtras);
    paginaAtual = 1;
    carregarMovimentacoes();
}

async function logout() {
    try {
        await apiFetch('auth/logout.php', { method: 'POST' });
        window.location.href = '../login/';
    } catch (e) {
        window.location.href = '../login/';
    }
}

function formatarData(dataString) {
    if (!dataString) return '-';
    const data = new Date(dataString + 'T00:00:00');
    return data.toLocaleDateString('pt-BR');
}

function formatarDataInput(data) {
    const ano = data.getFullYear();
    const mes = String(data.getMonth() + 1).padStart(2, '0');
    const dia = String(data.getDate()).padStart(2, '0');
    return `${ano}-${mes}-${dia}`;
}

function formatarDataHora(dataString) {
    if (!dataString) return '-';
    const data = new Date(dataString);
    return data.toLocaleString('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}