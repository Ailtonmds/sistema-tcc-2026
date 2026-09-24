// Categorias JavaScript - CRUD completo

document.addEventListener('DOMContentLoaded', () => {
    carregarCategorias();
    
    document.getElementById('btnLogout').addEventListener('click', logout);
    document.getElementById('formCategoria').addEventListener('submit', salvarCategoria);
    
    document.getElementById('modalCategoria').addEventListener('hidden.bs.modal', () => {
        document.getElementById('formCategoria').reset();
        document.getElementById('categoriaId').value = '';
        document.getElementById('modalCategoriaLabel').textContent = 'Nova Categoria';
        document.getElementById('btnSalvarCategoria').innerHTML = '<i class="bi bi-check-lg me-1"></i>Salvar';
    });
});

async function carregarCategorias() {
    try {
        const response = await apiFetch('categorias/listar.php');
        const data = await apiJson(response);
        
        const tbody = document.querySelector('#tabelaCategorias tbody');
        
        if (!data.sucesso || !data.dados || data.dados.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-secondary py-4">Nenhuma categoria encontrada</td></tr>';
            return;
        }
        
        tbody.innerHTML = data.dados.map(c => `
            <tr>
                <td><strong>${c.nome}</strong></td>
                <td>${c.descricao || '-'}</td>
                <td><span class="badge ${c.ativo ? 'bg-success' : 'bg-secondary'}">${c.ativo ? 'Ativa' : 'Inativa'}</span></td>
                <td class="text-end">
                    <button class="btn btn-sm btn-outline-primary" onclick="editarCategoria(${c.id})" title="Editar"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm btn-outline-danger" onclick="excluirCategoria(${c.id}, '${c.nome}')" title="Excluir"><i class="bi bi-trash"></i></button>
                </td>
            </tr>
        `).join('');
    } catch (e) {
        console.error('Erro ao carregar categorias:', e);
    }
}

function abrirModalNova() {
    document.getElementById('formCategoria').reset();
    document.getElementById('categoriaId').value = '';
    document.getElementById('modalCategoriaLabel').textContent = 'Nova Categoria';
    document.getElementById('btnSalvarCategoria').innerHTML = '<i class="bi bi-check-lg me-1"></i>Salvar';
    document.getElementById('ativo').checked = true;
}

async function editarCategoria(id) {
    try {
        const response = await apiFetch('categorias/listar.php');
        const data = await apiJson(response);
        
        if (!data.sucesso) return;
        
        const categoria = data.dados.find(c => c.id === id);
        if (!categoria) return;
        
        document.getElementById('categoriaId').value = categoria.id;
        document.getElementById('nome').value = categoria.nome;
        document.getElementById('descricao').value = categoria.descricao || '';
        document.getElementById('ativo').checked = categoria.ativo;
        
        document.getElementById('modalCategoriaLabel').textContent = 'Editar Categoria';
        document.getElementById('btnSalvarCategoria').innerHTML = '<i class="bi bi-check-lg me-1"></i>Atualizar';
        
        const modal = new bootstrap.Modal(document.getElementById('modalCategoria'));
        modal.show();
    } catch (e) {
        console.error('Erro ao carregar categoria:', e);
    }
}

async function salvarCategoria(e) {
    e.preventDefault();
    
    const id = document.getElementById('categoriaId').value;
    const isEdit = id !== '';
    
    const formData = new FormData(e.target);
    const dados = Object.fromEntries(formData.entries());
    dados.ativo = dados.ativo === 'on' ? 1 : 0;
    
    const url = isEdit ? 'categorias/editar.php' : 'categorias/cadastrar.php';
    
    const btn = document.getElementById('btnSalvarCategoria');
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
            bootstrap.Modal.getInstance(document.getElementById('modalCategoria')).hide();
            carregarCategorias();
            mostrarToast(isEdit ? 'Categoria atualizada com sucesso!' : 'Categoria cadastrada com sucesso!', 'success');
        } else {
            mostrarToast(result.mensagem, 'danger');
        }
    } catch (error) {
        mostrarToast('Erro ao salvar categoria.', 'danger');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

async function excluirCategoria(id, nome) {
    if (!confirm(`Tem certeza que deseja excluir a categoria "${nome}"?`)) return;
    
    try {
        const response = await apiFetch('categorias/excluir.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        
        const result = await apiJson(response);
        
        if (result.sucesso) {
            carregarCategorias();
            mostrarToast(result.mensagem, 'success');
        } else {
            mostrarToast(result.mensagem, 'danger');
        }
    } catch (error) {
        mostrarToast('Erro ao excluir categoria.', 'danger');
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