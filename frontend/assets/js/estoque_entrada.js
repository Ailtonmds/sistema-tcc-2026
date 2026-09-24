// Entrada de Estoque JavaScript

let produtosCache = [];

document.addEventListener('DOMContentLoaded', () => {
    carregarProdutos();
    
    document.getElementById('btnLogout').addEventListener('click', logout);
    document.getElementById('formEntrada').addEventListener('submit', registrarEntrada);
});

async function carregarProdutos() {
    try {
        const response = await apiFetch('produtos/listar.php?ativo=1');
        const data = await apiJson(response);
        if (data.sucesso) {
            produtosCache = data.dados;
            const select = document.getElementById('produto_id');
            select.innerHTML = '<option value="">Selecione um produto</option>' + 
                produtosCache.map(p => `<option value="${p.id}">${p.nome} (${p.unidade_medida})</option>`).join('');
        }
    } catch (e) {
        console.error('Erro ao carregar produtos:', e);
    }
}

async function carregarLotesDoProduto() {
    const produto_id = document.getElementById('produto_id').value;
    const loteSelect = document.getElementById('lote_id');
    const qtdDisponivel = document.getElementById('quantidade_disponivel');
    const validadeLote = document.getElementById('validade_lote');
    
    loteSelect.disabled = true;
    loteSelect.innerHTML = '<option value="">Carregando...</option>';
    qtdDisponivel.value = '';
    validadeLote.value = '';
    
    if (!produto_id) {
        loteSelect.innerHTML = '<option value="">Selecione um produto primeiro</option>';
        return;
    }
    
    try {
        const response = await apiFetch(`lotes/listar.php?produto_id=${produto_id}`);
        const data = await apiJson(response);
        
        if (data.sucesso && data.dados && data.dados.length > 0) {
            loteSelect.innerHTML = '<option value="">Selecione um lote</option>' + 
                data.dados.map(l => `<option value="${l.id}" data-qtd="${l.quantidade}" data-val="${l.data_validade}">${l.numero_lote} (Qtd: ${l.quantidade})</option>`).join('');
            loteSelect.disabled = false;
        } else {
            loteSelect.innerHTML = '<option value="">Nenhum lote cadastrado para este produto</option>';
        }
    } catch (e) {
        console.error('Erro ao carregar lotes:', e);
        loteSelect.innerHTML = '<option value="">Erro ao carregar lotes</option>';
    }
}

document.getElementById('lote_id').addEventListener('change', function() {
    const option = this.options[this.selectedIndex];
    const qtd = option.dataset.qtd;
    const val = option.dataset.val;
    
    document.getElementById('quantidade_disponivel').value = qtd ? `${qtd} unidades` : '';
    document.getElementById('validade_lote').value = val ? formatarData(val) : '';
});

async function registrarEntrada(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const dados = Object.fromEntries(formData.entries());
    dados.produto_id = parseInt(dados.produto_id);
    dados.lote_id = parseInt(dados.lote_id);
    dados.quantidade = parseInt(dados.quantidade);
    
    const btn = document.getElementById('btnRegistrarEntrada');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Registrando...';
    
    try {
        const response = await apiFetch('estoque/entrada.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(dados)
        });
        
        const result = await apiJson(response);
        
        if (result.sucesso) {
            mostrarToast('Entrada registrada com sucesso!', 'success');
            e.target.reset();
            document.getElementById('lote_id').disabled = true;
            document.getElementById('lote_id').innerHTML = '<option value="">Selecione um produto primeiro</option>';
            document.getElementById('quantidade_disponivel').value = '';
            document.getElementById('validade_lote').value = '';
        } else {
            mostrarToast(result.mensagem, 'danger');
        }
    } catch (error) {
        mostrarToast('Erro ao registrar entrada.', 'danger');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
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