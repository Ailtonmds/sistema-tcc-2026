// Relatórios JavaScript

let chartRelEstoqueCategoria = null;
let chartRelStatusEstoque = null;
let produtosCache = [];

document.addEventListener('DOMContentLoaded', () => {
    // Definir datas padrão para movimentações (últimos 30 dias)
    const hoje = new Date();
    const trintaDiasAtras = new Date();
    trintaDiasAtras.setDate(hoje.getDate() - 30);
    
    document.getElementById('rel_data_fim').value = formatarDataInput(hoje);
    document.getElementById('rel_data_inicio').value = formatarDataInput(trintaDiasAtras);
    
    carregarProdutosParaFiltros();
    carregarRelatorios();
    
    document.getElementById('btnLogout').addEventListener('click', logout);
    document.getElementById('formFiltroMov').addEventListener('submit', (e) => {
        e.preventDefault();
        carregarRelMovimentacoes();
    });
    
    // Carregar relatório quando a aba for ativada
    document.querySelectorAll('#relatoriosTabs button[data-bs-toggle="tab"]').forEach(tab => {
        tab.addEventListener('shown.bs.tab', (e) => {
            const target = e.target.getAttribute('data-bs-target');
            if (target === '#panel-estoque') carregarRelEstoque();
            else if (target === '#panel-movimentacoes') carregarRelMovimentacoes();
            else if (target === '#panel-validade') carregarRelValidade();
        });
    });
});

async function carregarProdutosParaFiltros() {
    try {
        const response = await apiFetch('produtos/listar.php?ativo=1');
        const data = await apiJson(response);
        if (data.sucesso) {
            produtosCache = data.dados;
            const options = produtosCache.map(p => `<option value="${p.id}">${p.nome}</option>`).join('');
            document.getElementById('rel_produto_id').innerHTML = '<option value="">Todos</option>' + options;
        }
    } catch (e) {
        console.error('Erro ao carregar produtos:', e);
    }
}

async function carregarRelatorios() {
    await Promise.all([
        carregarRelEstoque(),
        carregarRelMovimentacoes(),
        carregarRelValidade()
    ]);
}

async function carregarRelEstoque() {
    try {
        const response = await apiFetch('relatorios/estoque.php');
        const data = await apiJson(response);
        
        if (data.sucesso) {
            atualizarTabelaRelEstoque(data.dados.produtos);
            atualizarGraficosRelEstoque(data.dados.resumo_por_categoria, data.dados.produtos);
        }
    } catch (e) {
        console.error('Erro ao carregar relatório de estoque:', e);
    }
}

function atualizarTabelaRelEstoque(produtos) {
    const tbody = document.querySelector('#tabelaRelEstoque tbody');
    
    if (!produtos || produtos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-secondary py-4">Nenhum produto encontrado</td></tr>';
        return;
    }
    
    tbody.innerHTML = produtos.map(p => {
        let statusClass = 'bg-success';
        let statusText = 'OK';
        if (p.status_estoque === 'sem_estoque') {
            statusClass = 'bg-danger';
            statusText = 'Sem estoque';
        } else if (p.status_estoque === 'baixo') {
            statusClass = 'bg-warning text-dark';
            statusText = 'Baixo';
        }
        
        return `
            <tr>
                <td><strong>${p.nome}</strong></td>
                <td>${p.categoria}</td>
                <td>${p.unidade_medida}</td>
                <td>${p.estoque_minimo}</td>
                <td>${p.quantidade_total}</td>
                <td>${p.localizacao || '-'}</td>
                <td><span class="badge ${statusClass}">${statusText}</span></td>
            </tr>
        `;
    }).join('');
}

function atualizarGraficosRelEstoque(resumoCategoria, produtos) {
    // Gráfico 1: Estoque por categoria (Doughnut)
    const ctxCat = document.getElementById('chartRelEstoqueCategoria').getContext('2d');
    if (chartRelEstoqueCategoria) chartRelEstoqueCategoria.destroy();
    
    chartRelEstoqueCategoria = new Chart(ctxCat, {
        type: 'doughnut',
        data: {
            labels: resumoCategoria.map(c => c.categoria),
            datasets: [{
                data: resumoCategoria.map(c => c.quantidade_total),
                backgroundColor: ['#246b3a', '#1b542d', '#82d39c', '#123d24', '#4a9c66', '#6ec68a'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { position: 'bottom', labels: { padding: 15, font: { size: 11 } } }
            }
        }
    });
    
    // Gráfico 2: Status do estoque (Bar)
    const ctxStatus = document.getElementById('chartRelStatusEstoque').getContext('2d');
    if (chartRelStatusEstoque) chartRelStatusEstoque.destroy();
    
    const semEstoque = produtos.filter(p => p.status_estoque === 'sem_estoque').length;
    const baixo = produtos.filter(p => p.status_estoque === 'baixo').length;
    const ok = produtos.filter(p => p.status_estoque === 'ok').length;
    
    chartRelStatusEstoque = new Chart(ctxStatus, {
        type: 'bar',
        data: {
            labels: ['Sem Estoque', 'Estoque Baixo', 'OK'],
            datasets: [{
                label: 'Quantidade de Produtos',
                data: [semEstoque, baixo, ok],
                backgroundColor: ['#a33a3a', '#a66a08', '#246b3a'],
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });
}

async function carregarRelMovimentacoes() {
    const formData = new FormData(document.getElementById('formFiltroMov'));
    const params = new URLSearchParams();
    
    for (const [key, value] of formData.entries()) {
        if (value) params.append(key, value);
    }
    
    try {
        const response = await apiFetch(`relatorios/movimentacoes.php?${params.toString()}`);
        const data = await apiJson(response);
        
        if (data.sucesso) {
            atualizarTabelaRelMovimentacoes(data.dados.movimentacoes);
            atualizarResumoMovimentacoes(data.dados.resumo);
        }
    } catch (e) {
        console.error('Erro ao carregar relatório de movimentações:', e);
    }
}

function atualizarTabelaRelMovimentacoes(movimentacoes) {
    const tbody = document.querySelector('#tabelaRelMovimentacoes tbody');
    
    if (!movimentacoes || movimentacoes.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-secondary py-4">Nenhuma movimentação encontrada</td></tr>';
        return;
    }
    
    tbody.innerHTML = movimentacoes.map(mov => `
        <tr>
            <td>${formatarDataHora(mov.data_movimentacao)}</td>
            <td><strong>${mov.produto_nome}</strong></td>
            <td>${mov.numero_lote}</td>
            <td><span class="badge ${mov.tipo === 'entrada' ? 'bg-success' : 'bg-danger'}">${mov.tipo}</span></td>
            <td>${mov.quantidade}</td>
            <td>${mov.usuario_nome}</td>
            <td>${mov.observacao || '-'}</td>
        </tr>
    `).join('');
}

function atualizarResumoMovimentacoes(resumo) {
    document.getElementById('relTotalEntradas').textContent = resumo.entrada?.total_quantidade || 0;
    document.getElementById('relQtdEntradas').textContent = `${resumo.entrada?.total_movimentacoes || 0} movimentações`;
    document.getElementById('relTotalSaidas').textContent = resumo.saida?.total_quantidade || 0;
    document.getElementById('relQtdSaidas').textContent = `${resumo.saida?.total_movimentacoes || 0} movimentações`;
}

async function carregarRelValidade() {
    const status = document.getElementById('rel_validade_status').value;
    
    try {
        const response = await apiFetch(`relatorios/validade.php?status=${status}`);
        const data = await apiJson(response);
        
        if (data.sucesso) {
            atualizarTabelaRelValidade(data.dados.lotes);
            document.getElementById('relValVencidos').textContent = data.dados.estatisticas.vencidos;
            document.getElementById('relValProximos').textContent = data.dados.estatisticas.proximos;
            document.getElementById('relValOk').textContent = data.dados.estatisticas.ok;
        }
    } catch (e) {
        console.error('Erro ao carregar relatório de validade:', e);
    }
}

function atualizarTabelaRelValidade(lotes) {
    const tbody = document.querySelector('#tabelaRelValidade tbody');
    
    if (!lotes || lotes.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center text-secondary py-4">Nenhum lote encontrado</td></tr>';
        return;
    }
    
    tbody.innerHTML = lotes.map(l => {
        let statusClass = 'bg-success';
        let statusText = 'Válido';
        if (l.status_validade === 'vencido') {
            statusClass = 'bg-danger';
            statusText = 'Vencido';
        } else if (l.status_validade === 'proximo') {
            statusClass = 'bg-warning text-dark';
            statusText = 'Próximo';
        }
        
        return `
            <tr class="${l.status_validade === 'vencido' ? 'table-danger' : (l.status_validade === 'proximo' ? 'table-warning' : '')}">
                <td><strong>${l.produto_nome}</strong></td>
                <td>${l.categoria_nome}</td>
                <td>${l.numero_lote}</td>
                <td>${formatarData(l.data_fabricacao)}</td>
                <td>${formatarData(l.data_validade)}</td>
                <td>${l.dias_restantes >= 0 ? l.dias_restantes : Math.abs(l.dias_restantes)}</td>
                <td>${l.quantidade}</td>
                <td><span class="badge ${statusClass}">${statusText}</span></td>
            </tr>
        `;
    }).join('');
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