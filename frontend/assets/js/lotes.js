// Lotes JavaScript - CRUD completo

let produtosCache = [];

document.addEventListener('DOMContentLoaded', () => {
    carregarProdutosParaSelect();
    carregarLotes();
    
    document.getElementById('btnLogout').addEventListener('click', logout);
    document.getElementById('formLote').addEventListener('submit', salvarLote);
    
    document.getElementById('modalLote').addEventListener('hidden.bs.modal', () => {
        document.getElementById('formLote').reset();
        document.getElementById('loteId').value = '';
        document.getElementById('modalLoteLabel').textContent = 'Novo Lote';
        document.getElementById('btnSalvarLote').innerHTML = '<i class="bi bi-check-lg me-1"></i>Salvar';
    });
});

async function carregarProdutosParaSelect() {
    try {
        const response = await apiFetch('produtos/listar.php?ativo=1');
        const data = await apiJson(response);
        if (data.sucesso) {
            produtosCache = data.dados;
            popularSelectProdutos();
        }
    } catch (e) {
        console.error('Erro ao carregar produtos:', e);
    }
}

function popularSelectProdutos() {
    const selectProduto = document.getElementById('produto_id');
    const selectFiltro = document.getElementById('filtroProduto');
    
    const options = produtosCache.map(p => 
        `<option value="${p.id}">${p.nome}</option>`
    ).join('');
    
    selectProduto.innerHTML = '<option value="">Selecione um produto</option>' + options;
    selectFiltro.innerHTML = '<option value="">Todos os produtos</option>' + options;
}

async function carregarLotes() {
    const produto_id = document.getElementById('filtroProduto').value;
    const status = document.getElementById('filtroStatusValidade').value;
    
    let url = 'lotes/listar.php?';
    const params = new URLSearchParams();
    if (produto_id) params.append('produto_id', produto_id);
    url += params.toString();
    
    try {
        const response = await apiFetch(url);
        const data = await apiJson(response);
        
        const tbody = document.querySelector('#tabelaLotes tbody');
        
        if (!data.sucesso || !data.dados || data.dados.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-secondary py-4">Nenhum lote encontrado</td></tr>';
            return;
        }
        
        let lotes = data.dados;
        
        // Filtrar por status de validade no frontend
        if (status) {
            const hoje = new Date();
            hoje.setHours(0,0,0,0);
            const dataLimite = new Date();
            dataLimite.setDate(dataLimite.getDate() + 30);
            
            lotes = lotes.filter(l => {
                const validade = new Date(l.data_validade + 'T00:00:00');
                if (status === 'vencidos') return validade < hoje;
                if (status === 'proximos') return validade >= hoje && validade <= dataLimite;
                if (status === 'ok') return validade > dataLimite;
                return true;
            });
        }
        
        if (lotes.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-secondary py-4">Nenhum lote encontrado com o filtro selecionado</td></tr>';
            return;
        }
        
        tbody.innerHTML = lotes.map(l => {
            const validade = new Date(l.data_validade + 'T00:00:00');
            const hoje = new Date();
            hoje.setHours(0,0,0,0);
            const dataLimite = new Date();
            dataLimite.setDate(dataLimite.getDate() + 30);
            
            let statusClass = 'bg-success';
            let statusText = 'Válido';
            if (validade < hoje) {
                statusClass = 'bg-danger';
                statusText = 'Vencido';
            } else if (validade <= dataLimite) {
                statusClass = 'bg-warning text-dark';
                statusText = 'Próximo do vencimento';
            }
            
            return `
                <tr>
                    <td><strong>${l.produto_nome}</strong></td>
                    <td>${l.numero_lote}</td>
                    <td>${l.data_fabricacao ? formatarData(l.data_fabricacao) : '-'}</td>
                    <td>${formatarData(l.data_validade)}</td>
                    <td>${l.quantidade}</td>
                    <td><span class="badge ${statusClass}">${statusText}</span></td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-primary" onclick="editarLote(${l.id})" title="Editar"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-outline-danger" onclick="excluirLote(${l.id}, '${l.numero_lote}')" title="Excluir"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>
            `;
        }).join('');
    } catch (e) {
        console.error('Erro ao carregar lotes:', e);
    }
}

function abrirModalNovo() {
    document.getElementById('formLote').reset();
    document.getElementById('loteId').value = '';
    document.getElementById('modalLoteLabel').textContent = 'Novo Lote';
    document.getElementById('btnSalvarLote').innerHTML = '<i class="bi bi-check-lg me-1"></i>Salvar';
    document.getElementById('data_validade').min = formatarDataInput(new Date());
}

async function editarLote(id) {
    try {
        const response = await apiFetch('lotes/listar.php');
        const data = await apiJson(response);
        
        if (!data.sucesso) return;
        
        const lote = data.dados.find(l => l.id === id);
        if (!lote) return;
        
        document.getElementById('loteId').value = lote.id;
        document.getElementById('produto_id').value = lote.produto_id || '';
        document.getElementById('numero_lote').value = lote.numero_lote;
        document.getElementById('data_fabricacao').value = lote.data_fabricacao || '';
        document.getElementById('data_validade').value = lote.data_validade;
        document.getElementById('quantidade').value = lote.quantidade || 1;
        
        document.getElementById('modalLoteLabel').textContent = 'Editar Lote';
        document.getElementById('btnSalvarLote').innerHTML = '<i class="bi bi-check-lg me-1"></i>Atualizar';
        
        const modal = new bootstrap.Modal(document.getElementById('modalLote'));
        modal.show();
    } catch (e) {
        console.error('Erro ao carregar lote:', e);
    }
}

async function salvarLote(e) {
    e.preventDefault();
    
    const id = document.getElementById('loteId').value;
    const isEdit = id !== '';
    
    const formData = new FormData(e.target);
    const dados = Object.fromEntries(formData.entries());
    dados.produto_id = parseInt(dados.produto_id);
    dados.quantidade = parseInt(dados.quantidade);
    
    const url = isEdit ? 'lotes/editar.php' : 'lotes/cadastrar.php';
    
    const btn = document.getElementById('btnSalvarLote');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Salvando...';
    
    try {
        const response = await apiFetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(dados)
        });
        
        const result = await apiJson(response);
        
        if (result.sucesso) {
            bootstrap.Modal.getInstance(document.getElementById('modalLote')).hide();
            carregarLotes();
            mostrarToast(isEdit ? 'Lote atualizado com sucesso!' : 'Lote cadastrado com sucesso!', 'success');
        } else {
            mostrarToast(result.mensagem, 'danger');
        }
    } catch (error) {
        mostrarToast('Erro ao salvar lote.', 'danger');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

async function excluirLote(id, numero) {
    if (!confirm(`Tem certeza que deseja excluir o lote "${numero}"?`)) return;
    
    try {
        const response = await apiFetch('lotes/excluir.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        
        const result = await apiJson(response);
        
        if (result.sucesso) {
            carregarLotes();
            mostrarToast(result.mensagem, 'success');
        } else {
            mostrarToast(result.mensagem, 'danger');
        }
    } catch (error) {
        mostrarToast('Erro ao excluir lote.', 'danger');
    }
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

function mostrarToast(mensagem, tipo = 'info') {
    const toastContainer = document.getElementById('toastContainer') || criarToastContainer();
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${tipo} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${mensagem}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    toastContainer.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast, { delay: 3000 });
    bsToast.show();
    toast.addEventListener('hidden.bs.toast', () => toast.remove());
}

function criarToastContainer() {
    const container = document.createElement('div');
    container.id = 'toastContainer';
    container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
    container.style.zIndex = '9999';
    document.body.appendChild(container);
    return container;
}