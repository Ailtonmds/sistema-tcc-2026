// Dashboard JavaScript - Carrega dados da API e atualiza a interface

let chartEstoqueCategoria = null;
let chartEntradasSaidas = null;

document.addEventListener('DOMContentLoaded', async () => {
    await carregarDashboard();
    
    document.getElementById('btnLogout').addEventListener('click', async () => {
        try {
            await apiFetch('auth/logout.php', { method: 'POST' });
            window.location.href = '../login/';
        } catch (e) {
            window.location.href = '../login/';
        }
    });
});

async function carregarDashboard() {
    try {
        const response = await apiFetch('dashboard.php');
        const data = await apiJson(response);

        if (!data.sucesso) {
            console.error('Erro ao carregar dashboard:', data.mensagem);
            return;
        }

        const d = data.dados;
        const usuario = d.usuario || {};
        if (usuario.id) sessionStorage.setItem('usuario_id', String(usuario.id));
        if (usuario.nome) sessionStorage.setItem('usuario_nome', usuario.nome);
        if (usuario.nivel_acesso) sessionStorage.setItem('nivel_acesso', usuario.nivel_acesso);

        // Atualizar estatísticas
        document.getElementById('statTotalProdutos').textContent = d.estatisticas.total_produtos;
        document.getElementById('statTotalItens').textContent = d.estatisticas.total_itens_estoque;
        document.getElementById('statEstoqueBaixo').textContent = d.estatisticas.estoque_baixo;
        document.getElementById('statProximosValidade').textContent = d.estatisticas.proximos_validade;

        // Atualizar nome do usuário
        const usuarioNome = sessionStorage.getItem('usuario_nome') || 'Usuário';
        document.getElementById('usuarioNome').textContent = usuarioNome;

        // Atualizar movimentações recentes
        atualizarTabelaMovimentacoes(d.movimentacoes_recentes);

        // Atualizar gráficos
        atualizarGraficos(d.graficos);

    } catch (error) {
        console.error('Erro ao carregar dashboard:', error);
    }
}

function atualizarTabelaMovimentacoes(movimentacoes) {
    const tbody = document.querySelector('#tabelaMovimentacoes tbody');
    
    if (!movimentacoes || movimentacoes.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-secondary py-4">Nenhuma movimentação registrada</td></tr>';
        return;
    }

    tbody.innerHTML = movimentacoes.map(mov => `
        <tr>
            <td>${formatarData(mov.data_movimentacao)}</td>
            <td>${mov.produto_nome}</td>
            <td>${mov.numero_lote}</td>
            <td><span class="badge ${mov.tipo === 'entrada' ? 'bg-success' : 'bg-danger'}">${mov.tipo}</span></td>
            <td>${mov.quantidade}</td>
            <td>${mov.usuario_nome}</td>
        </tr>
    `).join('');
}

function atualizarGraficos(graficos) {
    // Gráfico de estoque por categoria (Doughnut)
    const ctxCat = document.getElementById('chartEstoqueCategoria').getContext('2d');
    if (chartEstoqueCategoria) chartEstoqueCategoria.destroy();
    
    chartEstoqueCategoria = new Chart(ctxCat, {
        type: 'doughnut',
        data: {
            labels: graficos.estoque_por_categoria.labels,
            datasets: [{
                data: graficos.estoque_por_categoria.data,
                backgroundColor: [
                    '#246b3a', '#1b542d', '#82d39c', '#123d24',
                    '#4a9c66', '#6ec68a', '#a8e6b8', '#cff2d9'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { padding: 15, font: { size: 12 } }
                }
            }
        }
    });

    // Gráfico de entradas vs saídas (Line)
    const ctxMov = document.getElementById('chartEntradasSaidas').getContext('2d');
    if (chartEntradasSaidas) chartEntradasSaidas.destroy();

    chartEntradasSaidas = new Chart(ctxMov, {
        type: 'line',
        data: {
            labels: graficos.entradas_saidas_30dias.labels,
            datasets: [
                {
                    label: 'Entradas',
                    data: graficos.entradas_saidas_30dias.entradas,
                    borderColor: '#246b3a',
                    backgroundColor: 'rgba(36, 107, 58, 0.1)',
                    fill: true,
                    tension: 0.3
                },
                {
                    label: 'Saídas',
                    data: graficos.entradas_saidas_30dias.saidas,
                    borderColor: '#a33a3a',
                    backgroundColor: 'rgba(163, 58, 58, 0.1)',
                    fill: true,
                    tension: 0.3
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { position: 'bottom', labels: { padding: 15, font: { size: 12 } } }
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });
}

function formatarData(dataString) {
    const data = new Date(dataString);
    return data.toLocaleString('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}