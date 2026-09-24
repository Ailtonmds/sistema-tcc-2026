// Usuários JavaScript - CRUD completo

document.addEventListener('DOMContentLoaded', () => {
    carregarUsuarios();
    
    document.getElementById('btnLogout').addEventListener('click', logout);
    document.getElementById('formUsuario').addEventListener('submit', salvarUsuario);
    
    document.getElementById('modalUsuario').addEventListener('hidden.bs.modal', () => {
        document.getElementById('formUsuario').reset();
        document.getElementById('usuarioId').value = '';
        document.getElementById('modalUsuarioLabel').textContent = 'Novo Usuário';
        document.getElementById('btnSalvarUsuario').innerHTML = '<i class="bi bi-check-lg me-1"></i>Salvar';
        document.getElementById('senha').required = true;
        document.getElementById('senhaHelp').textContent = 'Mínimo 6 caracteres';
    });
});

async function carregarUsuarios() {
    try {
        const response = await apiFetch('usuarios/listar.php');
        const data = await apiJson(response);
        
        const tbody = document.querySelector('#tabelaUsuarios tbody');
        
        if (!data.sucesso || !data.dados || data.dados.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-secondary py-4">Nenhum usuário encontrado</td></tr>';
            return;
        }
        
        tbody.innerHTML = data.dados.map(u => {
            // Não mostrar botão de excluir para o próprio usuário
            const isCurrentUser = Number(u.id) === getCurrentUserId();
            
            return `
                <tr>
                    <td><strong>${u.nome}</strong></td>
                    <td>${u.usuario}</td>
                    <td><span class="badge ${u.nivel_acesso === 'admin' ? 'bg-primary' : 'bg-secondary'}">${u.nivel_acesso === 'admin' ? 'Admin' : 'Operador'}</span></td>
                    <td><span class="badge ${u.ativo ? 'bg-success' : 'bg-secondary'}">${u.ativo ? 'Ativo' : 'Inativo'}</span></td>
                    <td>${formatarDataHora(u.criado_em)}</td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-primary" onclick="editarUsuario(${u.id})" title="Editar"><i class="bi bi-pencil"></i></button>
                        ${!isCurrentUser ? `<button class="btn btn-sm btn-outline-danger" onclick="excluirUsuario(${u.id}, '${u.nome}')" title="Excluir"><i class="bi bi-trash"></i></button>` : ''}
                    </td>
                </tr>
            `;
        }).join('');
    } catch (e) {
        console.error('Erro ao carregar usuários:', e);
    }
}

function getCurrentUserId() {
    return Number(sessionStorage.getItem('usuario_id')) || null;
}

function abrirModalNovo() {
    document.getElementById('formUsuario').reset();
    document.getElementById('usuarioId').value = '';
    document.getElementById('modalUsuarioLabel').textContent = 'Novo Usuário';
    document.getElementById('btnSalvarUsuario').innerHTML = '<i class="bi bi-check-lg me-1"></i>Salvar';
    document.getElementById('senha').required = true;
    document.getElementById('senhaHelp').textContent = 'Mínimo 6 caracteres';
    document.getElementById('ativo').checked = true;
}

async function editarUsuario(id) {
    try {
        const response = await apiFetch('usuarios/listar.php');
        const data = await apiJson(response);
        
        if (!data.sucesso) return;
        
        const usuario = data.dados.find(u => u.id === id);
        if (!usuario) return;
        
        document.getElementById('usuarioId').value = usuario.id;
        document.getElementById('nome').value = usuario.nome;
        document.getElementById('usuario').value = usuario.usuario;
        document.getElementById('nivel_acesso').value = usuario.nivel_acesso;
        document.getElementById('ativo').checked = usuario.ativo;
        
        // Senha não é preenchida por segurança
        document.getElementById('senha').value = '';
        document.getElementById('senha').required = false;
        document.getElementById('senhaHelp').textContent = 'Deixe em branco para não alterar';
        
        document.getElementById('modalUsuarioLabel').textContent = 'Editar Usuário';
        document.getElementById('btnSalvarUsuario').innerHTML = '<i class="bi bi-check-lg me-1"></i>Atualizar';
        
        const modal = new bootstrap.Modal(document.getElementById('modalUsuario'));
        modal.show();
    } catch (e) {
        console.error('Erro ao carregar usuário:', e);
    }
}

async function salvarUsuario(e) {
    e.preventDefault();
    
    const id = document.getElementById('usuarioId').value;
    const isEdit = id !== '';
    
    const formData = new FormData(e.target);
    const dados = Object.fromEntries(formData.entries());
    dados.ativo = dados.ativo === 'on' ? 1 : 0;
    
    // Se estiver editando e senha estiver vazia, não enviar
    if (isEdit && !dados.senha) {
        delete dados.senha;
    }
    
    const url = isEdit ? 'usuarios/editar.php' : 'usuarios/cadastrar.php';
    
    const btn = document.getElementById('btnSalvarUsuario');
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
            bootstrap.Modal.getInstance(document.getElementById('modalUsuario')).hide();
            carregarUsuarios();
            mostrarToast(isEdit ? 'Usuário atualizado com sucesso!' : 'Usuário cadastrado com sucesso!', 'success');
        } else {
            mostrarToast(result.mensagem, 'danger');
        }
    } catch (error) {
        mostrarToast('Erro ao salvar usuário.', 'danger');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

async function excluirUsuario(id, nome) {
    if (!confirm(`Tem certeza que deseja excluir/desativar o usuário "${nome}"?`)) return;
    
    try {
        const response = await apiFetch('usuarios/excluir.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        
        const result = await apiJson(response);
        
        if (result.sucesso) {
            carregarUsuarios();
            mostrarToast(result.mensagem, 'success');
        } else {
            mostrarToast(result.mensagem, 'danger');
        }
    } catch (error) {
        mostrarToast('Erro ao excluir usuário.', 'danger');
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

function formatarDataHora(dataString) {
    if (!dataString) return '-';
    const data = new Date(dataString);
    return data.toLocaleString('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
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