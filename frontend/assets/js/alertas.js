// Alertas JavaScript

document.addEventListener('DOMContentLoaded', () => {
    carregarAlertas();
    
    document.getElementById('btnLogout').addEventListener('click', logout);
});

async function carregarAlertas() {
    try {
        // Estoque baixo
        const responseBaixo = await apiFetch('alertas/estoque_baixo.php');
        const dataBaixo = await apiJson(responseBaixo);
        
        if (dataBaixo.sucesso) {
            atualizarTabelaEstoqueBaixo(dataBaixo.dados);
            document.getElementById('totalEstoqueBaixo').textContent = dataBaixo.total;
        }
        
        // Validade (próximos e vencidos)
        const responseValidade = await apiFetch('alertas/validade.php');
        const dataValidade = await apiJson(responseValidade);
        
        if (dataValidade.sucesso) {
            atualizarTabelaProximos(dataValidade.dados.proximos_vencimento);
            atualizarTabelaVencidos(dataValidade.dados.vencidos);
            document.getElementById('totalProximosValidade').textContent = dataValidade.total_proximos;
            document.getElementById('totalVencidos').textContent = dataValidade.total_vencidos;
        }
        
        const total = (dataBaixo.total || 0) + (dataValidade.total_proximos || 0) + (dataValidade.total_vencidos || 0);
        document.getElementById('totalAlertas').textContent = total;
        
    } catch (e) {
        console.error('Erro ao carregar alertas:', e);
    }
}

function atualizarTabelaEstoqueBaixo(produtos) {
    const tbody = document.querySelector('#tabelaEstoqueBaixo tbody');
    
    if (!produtos || produtos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-secondary py-4">Nenhum produto com estoque baixo</td></tr>';
        return;
    }
    
    tbody.innerHTML = produtos.map(p => `
        <tr class="table-warning">
            <td><strong>${p.nome}</strong></td>
            <td>${p.categoria_nome}</td>
            <td><span class="fw-bold text-danger">${p.quantidade_total}</span></td>
            <td>${p.estoque_minimo}</td>
            <td>${p.unidade_medida}</td>
            <td>${p.localizacao || '-'}</td>
        </tr>
    `).join('');
}

function atualizarTabelaProximos(lotes) {
    const tbody = document.querySelector('#tabelaProximosValidade tbody');
    
    if (!lotes || lotes.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-secondary py-4">Nenhum lote próximo do vencimento</td></tr>';
        return;
    }
    
    tbody.innerHTML = lotes.map(l => `
        <tr class="table-warning">
            <td><strong>${l.produto_nome}</strong></td>
            <td>${l.categoria_nome}</td>
            <td>${l.numero_lote}</td>
            <td>${formatarData(l.data_validade)}</td>
            <td><span class="badge bg-warning text-dark">${l.dias_restantes} dias</span></td>
            <td>${l.quantidade}</td>
        </tr>
    `).join('');
}

function atualizarTabelaVencidos(lotes) {
    const tbody = document.querySelector('#tabelaVencidos tbody');
    
    if (!lotes || lotes.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-secondary py-4">Nenhum lote vencido</td></tr>';
        return;
    }
    
    tbody.innerHTML = lotes.map(l => `
        <tr class="table-danger">
            <td><strong>${l.produto_nome}</strong></td>
            <td>${l.categoria_nome}</td>
            <td>${l.numero_lote}</td>
            <td>${formatarData(l.data_validade)}</td>
            <td><span class="badge bg-danger">${Math.abs(l.dias_restantes)} dias</span></td>
            <td>${l.quantidade}</td>
        </tr>
    `).join('');
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