// Produtos JavaScript - CRUD completo

let buscaTimeout = null;
let categoriasCache = [];

document.addEventListener('DOMContentLoaded', () => {
    carregarCategorias();
    carregarProdutos();
    
    document.getElementById('btnLogout').addEventListener('click', logout);
    document.getElementById('formProduto').addEventListener('submit', salvarProduto);
    
    // Limpar formulário ao fechar modal
    document.getElementById('modalProduto').addEventListener('hidden.bs.modal', () => {
        document.getElementById('formProduto').reset();
        document.getElementById('produtoId').value = '';
        document.getElementById('modalProdutoLabel').textContent = 'Novo Produto';
        document.getElementById('btnSalvarProduto').innerHTML = '<i class="bi bi-check-lg me-1"></i>Salvar';
    });
});

async function carregarCategorias() {
    try {
        const response = await apiFetch('categorias/listar.php');
        const data = await apiJson(response);
        if (data.sucesso) {
            categoriasCache = data.dados;
            popularSelectCategorias();
        }
    } catch (e) {
        console.error('Erro ao carregar categorias:', e);
    }
}

function popularSelectCategorias() {
    const selectCategoria = document.getElementById('categoria_id');
    const selectFiltro = document.getElementById('filtroCategoria');
    
    const options = categoriasCache.filter(c => c.ativo).map(c => 
        `<option value="${c.id}">${c.nome}</option>`
    ).join('');
    
    selectCategoria.innerHTML = '<option value="">Selecione</option>' + options;
    selectFiltro.innerHTML = '<option value="">Todas as categorias</option>' + options;
}

function debounceBusca() {
    clearTimeout(buscaTimeout);
    buscaTimeout = setTimeout(carregarProdutos, 300);
}

async function carregarProdutos() {
    const busca = document.getElementById('buscaProduto').value;
    const categoria_id = document.getElementById('filtroCategoria').value;
    const ativo = document.getElementById('filtroStatus').value;
    
    let url = 'produtos/listar.php?';
    const params = new URLSearchParams();
    if (busca) params.append('busca', busca);
    if (categoria_id) params.append('categoria_id', categoria_id);
    if (ativo !== '') params.append('ativo', ativo);
    url += params.toString();
    
    try {
        const response = await apiFetch(url);
        const data = await apiJson(response);
        
        const tbody = document.querySelector('#tabelaProdutos tbody');
        
        if (!data.sucesso || !data.dados || data.dados.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center text-secondary py-4">Nenhum produto encontrado</td></tr>';
            return;
        }
        
        tbody.innerHTML = data.dados.map(p => `
            <tr>
                <td><strong>${p.nome}</strong><br><small class="text-secondary">${p.descricao || ''}</small></td>
                <td>${p.categoria_nome}</td>
                <td>${p.unidade_medida}</td>
                <td>${p.estoque_minimo}</td>
                <td>${p.quantidade_total}</td>
                <td>${p.localizacao || '-'}</td>
                <td><span class="badge ${p.ativo ? 'bg-success' : 'bg-secondary'}">${p.ativo ? 'Ativo' : 'Inativo'}</span></td>
                <td class="text-end">
                    <button class="btn btn-sm btn-outline-primary" onclick="editarProduto(${p.id})" title="Editar"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm btn-outline-danger" onclick="excluirProduto(${p.id}, '${p.nome}')" title="Excluir"><i class="bi bi-trash"></i></button>
                </td>
            </tr>
        `).join('');
    } catch (e) {
        console.error('Erro ao carregar produtos:', e);
    }
}

function abrirModalNovo() {
    document.getElementById('formProduto').reset();
    document.getElementById('produtoId').value = '';
    document.getElementById('modalProdutoLabel').textContent = 'Novo Produto';
    document.getElementById('btnSalvarProduto').innerHTML = '<i class="bi bi-check-lg me-1"></i>Salvar';
    document.getElementById('ativo').checked = true;
}

async function editarProduto(id) {
    try {
        const response = await apiFetch('produtos/listar.php');
        const data = await apiJson(response);
        
        if (!data.sucesso) return;
        
        const produto = data.dados.find(p => Number(p.id) === Number(id));
        if (!produto) return;
        
        document.getElementById('produtoId').value = produto.id;
        document.getElementById('nome').value = produto.nome;
        document.getElementById('descricao').value = produto.descricao || '';
        document.getElementById('categoria_id').value = produto.categoria_id;
        document.getElementById('unidade_medida').value = produto.unidade_medida;
        document.getElementById('estoque_minimo').value = produto.estoque_minimo;
        document.getElementById('localizacao').value = produto.localizacao || '';
        document.getElementById('ativo').checked = produto.ativo;
        
        document.getElementById('modalProdutoLabel').textContent = 'Editar Produto';
        document.getElementById('btnSalvarProduto').innerHTML = '<i class="bi bi-check-lg me-1"></i>Atualizar';
        
        const modal = new bootstrap.Modal(document.getElementById('modalProduto'));
        modal.show();
    } catch (e) {
        console.error('Erro ao carregar produto:', e);
    }
}

async function salvarProduto(e) {
    e.preventDefault();
    
    const id = document.getElementById('produtoId').value;
    const isEdit = id !== '';
    
    const formData = new FormData(e.target);
    const dados = Object.fromEntries(formData.entries());
    dados.ativo = dados.ativo === 'on' ? 1 : 0;
    dados.estoque_minimo = parseInt(dados.estoque_minimo) || 0;
    dados.categoria_id = parseInt(dados.categoria_id);
    
    const url = isEdit ? 'produtos/editar.php' : 'produtos/cadastrar.php';
    
    const btn = document.getElementById('btnSalvarProduto');
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
            bootstrap.Modal.getInstance(document.getElementById('modalProduto')).hide();
            carregarProdutos();
            mostrarToast(isEdit ? 'Produto atualizado com sucesso!' : 'Produto cadastrado com sucesso!', 'success');
        } else {
            mostrarToast(result.mensagem, 'danger');
        }
    } catch (error) {
        mostrarToast('Erro ao salvar produto.', 'danger');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

async function excluirProduto(id, nome) {
    if (!confirm(`Tem certeza que deseja excluir/desativar o produto "${nome}"?`)) return;
    
    try {
        const response = await apiFetch('produtos/excluir.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        
        const result = await apiJson(response);
        
        if (result.sucesso) {
            carregarProdutos();
            mostrarToast(result.mensagem, 'success');
        } else {
            mostrarToast(result.mensagem, 'danger');
        }
    } catch (error) {
        mostrarToast('Erro ao excluir produto.', 'danger');
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