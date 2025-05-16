/**
 * Kanban Demo App: Projects, Employees, Tasks (Kanban)
 * Корректная инициализация demo-данных, первичный рендер всех таблиц/канбан, правильное навешивание обработчиков ДО или сразу после загрузки DOM.
 *
 * Обновлено: теперь у сотрудников есть поле 'role' (admin/user), а также поле 'bio' для биографии.
 * Один из сотрудников — админ, остальные — обычные пользователи.
 */

// Примерные проекты
let projects = [
  { id: 1, name: 'Kanban Board', desc: 'Веб Kanban для команд', type: 'Открытый', users: 3 },
  { id: 2, name: 'HR-портал', desc: 'Внутренний HR-инструмент', type: 'Закрытый', users: 2 },
];
let editProjectId = null;

// Примерные сотрудники
let employees = [
  { id: 1, name: 'Иван Иванов', email: 'ivan@company.ru', role: 'admin', bio: 'Руководитель IT-проекта. Люблю Agile и Kanban.' },
  { id: 2, name: 'Мария Климова', email: 'maria@company.ru', role: 'user', bio: 'Тимлид, ответственная за backend.' },
  { id: 3, name: 'Ольга Новикова', email: 'olga@company.ru', role: 'user', bio: 'Аналитик продукта.' }
];

// --- Kanban: задачи, раздел "Мои задачи" ---
let tasks = [
  {
    id: 1,
    name: 'Фронтенд для доски',
    desc: 'Сделать drag&drop, плавные анимации и чеклист',
    date: '2025-05-20',
    status: 'inprogress',
    priority: 'high',
    assignee: 'Иван Иванов',
    subtasks: [
      { id: 101, text: 'Drag & Drop', done: true },
      { id: 102, text: 'Плавные анимации', done: false },
      { id: 103, text: 'Подзадачи', done: true }
    ]
  },
  {
    id: 2,
    name: 'Базовый backend',
    desc: 'Прокинуть LocalStorage, предусмотреть API',
    date: '2025-05-17',
    status: 'review',
    priority: 'medium',
    assignee: 'Мария Климова',
    subtasks: [
      { id: 201, text: 'LocalStorage сохранение', done: true },
      { id: 202, text: 'Тест API', done: false }
    ]
  },
  {
    id: 3,
    name: 'Документация по запуску',
    desc: 'Пошаговый гайд для пользователей',
    date: '2025-05-13',
    status: 'done',
    priority: 'low',
    assignee: 'Иван Иванов',
    subtasks: [
      { id: 301, text: 'Гайд для задач', done: true }
    ]
  },
  {
    id: 4,
    name: 'Собеседование с командой',
    desc: 'Определить роли и функционал',
    date: '2025-05-21',
    status: 'new',
    priority: 'medium',
    assignee: 'Ольга Новикова',
    subtasks: []
  }
];
let editTaskId = null;
let taskSubtasks = [];

// --- Для генерации уникальных id для подзадач ---
function getRandomId() { return Math.floor(Math.random() * 100000) + Date.now(); }

// --- Рендер проектов ---
function renderProjects() {
    const tbody = document.querySelector('.projects .table-block tbody');
    if (!tbody) return;
    tbody.innerHTML = '';
    projects.forEach(pr => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
        <td>${pr.name}</td>
        <td>${pr.type}</td>
        <td>${pr.desc}</td>
        <td>${pr.users}</td>
        <td>
          <button class="btn mini edit-project-btn" data-id="${pr.id}" title="Редактировать">✎</button>
          <button class="btn mini danger delete-project-btn" data-id="${pr.id}" title="Удалить">🗑</button>
        </td>`;
        tbody.appendChild(tr);
    });
}

// --- Рендер сотрудников ---
function renderEmployees() {
  const tbody = document.querySelector('.employees .table-block tbody');
  if (!tbody) return;
  tbody.innerHTML = '';
  // текущий пользователь (для проверки роли/кнопок)
  // Используем глобальный currentUserId, если он есть, иначе по умолчанию 1
  let currentUserId = window.currentUserId || 1;
  const currentUser = employees.find(e => e.id === currentUserId);
  employees.forEach(emp => {
    const tr = document.createElement('tr');
    let roleCell = '';
    // Если admin — дать возможность смены роли и редактировать (кроме себя)
    if (currentUser && currentUser.role === 'admin' && emp.id !== currentUser.id) {
      roleCell = `<select class="role-select" data-id="${emp.id}">
        <option value="user"${emp.role==='user'?' selected':''}>user</option>
        <option value="admin"${emp.role==='admin'?' selected':''}>admin</option>
      </select>`;
      tr.innerHTML = `<td>${emp.name}</td><td>${emp.email}</td><td>${roleCell}</td>
        <td><button class="btn mini edit-emp-btn" data-id="${emp.id}" title="Редактировать">✎</button></td>`;
    } else {
      // Только просмотр
      tr.innerHTML = `<td>${emp.name}</td><td>${emp.email}</td><td>${emp.role}</td><td></td>`;
    }
    tbody.appendChild(tr);
  });

  // Навешиваем обработчики на select'ы смены роли и кнопки "Редактировать"
  tbody.querySelectorAll('.role-select').forEach(sel => {
    sel.onchange = function() {
      const id = +this.dataset.id;
      const emp = employees.find(e => e.id === id);
      if (emp) {
        emp.role = this.value;
        renderEmployees();
      }
    };
  });
  tbody.querySelectorAll('.edit-emp-btn').forEach(btn => {
    btn.onclick = function() {
      const id = +this.dataset.id;
      const emp = employees.find(e => e.id === id);
      const modalEmp = document.getElementById('modal-employee');
      const empForm = document.getElementById('employee-form');
      const empCancel = document.getElementById('employee-cancel');
      if (!modalEmp || !empForm || !emp) return;
      // Автозаполнение
      empForm['emp-name'].value = emp.name;
      empForm['emp-email'].value = emp.email;
      empForm['emp-bio'].value = emp.bio || '';
      empForm['emp-role'].value = emp.role;
      modalEmp.style.display = 'flex';

      // Очистить старые обработчики (разово)
      empCancel.onclick = function() {
        modalEmp.style.display = 'none';
        empForm.reset();
      };
      empForm.onsubmit = function(e) {
        e.preventDefault();
        emp.name = empForm['emp-name'].value.trim();
        emp.email = empForm['emp-email'].value.trim();
        emp.bio = empForm['emp-bio'].value.trim();
        emp.role = empForm['emp-role'].value;
        renderEmployees();
        modalEmp.style.display = 'none';
        empForm.reset();
      };
    };
  });
}

// --- Kanban: Drag&Drop ---
function setUpDragAndDrop() {
    document.querySelectorAll('.kanban-card').forEach(card => {
        card.setAttribute('draggable', 'true');
        card.ondragstart = function(e) {
            card.classList.add('dragging');
            e.dataTransfer.setData('text/plain', card.querySelector('.edit-task-btn').dataset.id);
            setTimeout(() => card.style.opacity = '.2', 0);
        };
        card.ondragend = function() {
            card.classList.remove('dragging');
            card.style.opacity = '';
        }
    });
    document.querySelectorAll('.kanban-cards').forEach(col => {
        col.ondragover = function(e) { e.preventDefault(); col.classList.add('drop-hover'); };
        col.ondragleave = function() { col.classList.remove('drop-hover'); };
        col.ondrop = function(e) {
            e.preventDefault();
            col.classList.remove('drop-hover');
            const id = +e.dataTransfer.getData('text/plain');
            const status = col.id.replace('tasks-', '');
            tasks = tasks.map(t=>t.id===id ? { ...t, status } : t);
            renderTasks();
            setUpDragAndDrop();
        };
    })
}

// --- Kanban: показать количество задач над каждой колонкой ---
function updateKanbanColCounters() {
    const counts = {new:0, inprogress:0, review:0, done:0};
    let visibleTasks = tasks;
    const q = kanbanSearchQuery ? kanbanSearchQuery.trim().toLowerCase() : '';
    if (q.length > 0) {
        visibleTasks = tasks.filter(t => t.name.toLowerCase().includes(q));
    }
    visibleTasks.forEach(t => counts[t.status] = (counts[t.status]||0)+1);
    [
        {s:'new', title:'Новые'},
        {s:'inprogress', title:'В процессе'},
        {s:'review', title:'Можно проверять'},
        {s:'done', title:'Готово'}
    ].forEach(col => {
        const el = document.querySelector('.kanban-col[data-status="'+col.s+'"] .kanban-col-title');
        if (el) el.innerHTML = col.title+' <span class="col-count">'+counts[col.s]+'</span>';
    });
}

// --- Kanban: фильтрация задач по поиску ---
let kanbanSearchQuery = '';

function getStatusTitle(st) {
  return {
    'new': 'Новые', 'inprogress': 'В процессе', 'review': 'Можно проверять', 'done': 'Готово',
  }[st] || st;
}

// --- Kanban: рендер задач ---
function origRenderTasks() {
    let visibleTasks = tasks;
    const q = kanbanSearchQuery.trim().toLowerCase();
    if (q.length > 0) {
        visibleTasks = tasks.filter(t => t.name.toLowerCase().includes(q));
    }
    ['new','inprogress','review','done'].forEach(st => {
        const col = document.getElementById('tasks-'+st);
        if (col) col.innerHTML = '';
    });
    const tasksByStatus = { new: [], inprogress: [], review: [], done: [] };
    visibleTasks.forEach(t => { if (tasksByStatus[t.status]) tasksByStatus[t.status].push(t); });
    Object.entries(tasksByStatus).forEach(([status, arr]) => {
        const col = document.getElementById('tasks-'+status);
        if (!col) return;
        if (arr.length === 0) {
            col.innerHTML = `<div class='kanban-empty' style='color:#b1b5be;text-align:center;padding:2.2em .5em 1.3em;opacity:.72;font-size:1.1em;user-select:none;'><div style='font-size:1.7em;margin-bottom:.1em;'>📭</div>Нет задач</div>`;
        } else {
            arr.forEach(t => {
                const card = document.createElement('div');
                card.className = 'kanban-card kanban-fade-in';
                const statusColor = {
                    'new': '#c5dafe', 'inprogress': '#ffe8b0', 'review': '#fad5b3', 'done': '#bff8db',
                }[t.status] || '#f0f0f0';
                let priorityColor = {
                  high: '#e74c3c',
                  medium: '#f1c40f',
                  low: '#2ecc71'
                }[t.priority] || '#b0b6c5';
                card.innerHTML = `
                  <div class="kanban-card-column">
                    <div class="status-dot-wrapper"><span class="status-dot" title="${getStatusTitle(t.status)}" style="background:${statusColor};"></span></div>
                    <div class="card-title">${t.name}</div>
                    ${t.priority ? `<div class="mini-indicator" title="Приоритет" style="color:${priorityColor};font-weight:bold;">Приоритет: ${t.priority[0].toUpperCase()}</div>` : ''}
                    ${t.assignee ? `<div class="mini-indicator" title="Исполнитель" style="color:#497;">👤 ${t.assignee}</div>` : ''}
                    ${t.date ? `<div class="mini-indicator" title="Дата"><span style='color:#888;'>🗓</span> <span style='color:#497;'>${t.date}</span></div>` : ''}
                    ${t.desc ? `<div class="card-desc">${t.desc}</div>` : ''}
                    ${(t.subtasks && t.subtasks.length) ? `<ul class='kanban-subtasks'>`+
                      t.subtasks.map(st=>`<li><input type='checkbox' ${st.done?'checked':''} disabled> <span style='${st.done?'text-decoration:line-through;color:#aaa;':''}'>${st.text}</span></li>`).join('')
                    +'</ul>' : ''}
                    <div class="card-actions">
                      <button class="btn mini edit-task-btn" data-id="${t.id}" title="Редактировать">✎</button>
                      <button class="btn mini danger delete-task-btn" data-id="${t.id}" title="Удалить">🗑</button>
                    </div>
                  </div>
                `;
                col.appendChild(card);
                setTimeout(() => card.classList.remove('kanban-fade-in'), 440);
            });
        }
    });
}

// --- Kanban: после рендера навешиваем drag&drop, обновляем счетчики ---
function renderTasks() {
    origRenderTasks();
    setUpDragAndDrop();
    updateKanbanColCounters();
    setUpKanbanButtons();
}

// --- КНОПКИ КАНБАНА: всегда рабочие edit/delete/create вне зависимости от способа добавления ---
function setUpKanbanButtons() {
  document.querySelectorAll('.kanban-card').forEach(card => {
    let editB = card.querySelector('.edit-task-btn');
    let delB = card.querySelector('.delete-task-btn');
    if (editB) editB.onclick = function(e) {
      e.stopPropagation();
      const id = +editB.dataset.id;
      window.openTaskModal('edit', id);
    };
    if (delB) delB.onclick = function(e) {
      e.preventDefault();
      card.classList.add('kanban-fade-out');
      card.addEventListener('animationend', function handler() {
        card.removeEventListener('animationend', handler);
        const idx = tasks.findIndex(task => task.id === +delB.dataset.id);
        if(idx !== -1) { tasks.splice(idx, 1); renderTasks(); }
      });
    };
  });
}

// --- Модалка проекта ---
window.openProjectModal = function(mode, projectId) {
    const modal = document.getElementById('modal-project');
    const form = document.getElementById('project-form');
    const title = document.getElementById('project-form-title');
    if (!modal || !form || !title) return;
    form.reset();
    editProjectId = null;
    if (mode === 'edit' && projectId) {
        title.textContent = 'Редактировать проект';
        const pr = projects.find(p => p.id === projectId);
        if (pr) {
            form['proj-name'].value = pr.name;
            form['proj-desc'].value = pr.desc;
            form['proj-public'].value = pr.type;
            editProjectId = projectId;
        }
    } else {
        title.textContent = 'Создать проект';
    }
    modal.style.display = 'flex';
    setTimeout(() => { form['proj-name'].focus(); }, 180);
}
window.closeProjectModal = function() {
    document.getElementById('modal-project').style.display = 'none';
    editProjectId = null;
};

// --- Модалка задачи ---
window.openTaskModal = function(mode, taskId) {
    const modal = document.getElementById('modal-task');
    const form = document.getElementById('task-form');
    const title = document.getElementById('task-form-title');
    form.reset();
    editTaskId = null;
    taskSubtasks = [];
    if (mode === 'edit' && taskId) {
        title.textContent = 'Редактировать задачу';
        const t = tasks.find(t => t.id === taskId);
        if (t) {
            form['task-name'].value = t.name;
            form['task-desc'].value = t.desc;
            form['task-date'].value = t.date;
            form['task-status'].value = t.status;
            editTaskId = taskId;
            taskSubtasks = (t.subtasks||[]).map(s=>({...s}));
        }
    } else {
        title.textContent = 'Создать задачу';
        form['task-status'].value = 'new';
        taskSubtasks = [];
    }
    renderSubtasksUI();
    modal.style.display = 'flex';
    setTimeout(() => { form['task-name'].focus(); }, 180);
}

// --- Явное закрытие модалки задачи ---
window.closeTaskModal = function() {
  const modal = document.getElementById('modal-task');
  if(modal) {
    modal.style.display = 'none';
    // По желанию делать очистку/сброс внутренних переменных, напр.:
    // editTaskId = null; taskSubtasks = [];
  }
};

// --- Рендер подзадач в модалке задачи ---
function renderSubtasksUI() {
    const ul = document.getElementById('subtasks-list');
    if (!ul) return;
    ul.innerHTML = '';
    if (!taskSubtasks.length) {
      ul.innerHTML = '<li style="color:#b8b8b8;font-size:.98em;">Подзадачи не добавлены</li>';
    } else {
      taskSubtasks.forEach((st, i) => {
        const li = document.createElement('li');
        li.style.display = 'flex';
        li.style.alignItems = 'center';
        li.style.gap = '0.6em';
        li.innerHTML = `<input type='checkbox' ${st.done?"checked":""} style='margin-right:4px;' data-index='${i}'>
          <span style='flex:1 1 auto;'>${st.text}</span>
          <button class='btn mini danger' style='padding:0 7px;' data-index='${i}'>✖</button>`;
        li.querySelector('button').onclick = (e) => {
          taskSubtasks.splice(i,1); renderSubtasksUI();
        };
        li.querySelector('input[type=checkbox]').onchange = (e)=>{
          taskSubtasks[i].done = e.target.checked;
        }
        ul.appendChild(li);
      });
    }
}

// --- Универсальное модальное окно ---
function showModal(type, title, message, onOk) {
    const modal = document.getElementById('modal');
    const icon = document.getElementById('modal-icon');
    const t = document.getElementById('modal-title');
    const msg = document.getElementById('modal-message');
    const btnOk = document.getElementById('modal-ok');
    const btnCancel = document.getElementById('modal-cancel');

    let ic = 'ℹ️';
    if (type === 'error') ic = '❗';
    else if (type === 'warning') ic = '⚠️';
    else if (type === 'success') ic = '✅';
    icon.textContent = ic;
    t.textContent = title;
    msg.textContent = message;

    modal.style.display = 'flex';

    function closeHandler() {
        modal.style.display = 'none';
        btnOk.removeEventListener('click', okHandler);
        btnCancel.removeEventListener('click', cancelHandler);
    }
    function okHandler() {
        closeHandler();
        if (typeof onOk === 'function') onOk();
    }
    function cancelHandler() {
        closeHandler();
    }
    btnOk.addEventListener('click', okHandler);
    btnCancel.addEventListener('click', cancelHandler);
}

// --- Скрытие модального окна вручную ---
function hideModal() {
    const modal = document.getElementById('modal');
    if (modal) {
        modal.style.display = 'none';
        const btnOk = document.getElementById('modal-ok');
        const btnCancel = document.getElementById('modal-cancel');
        if (btnOk) {
            const newBtnOk = btnOk.cloneNode(true);
            btnOk.parentNode.replaceChild(newBtnOk, btnOk);
        }
        if (btnCancel) {
            const newBtnCancel = btnCancel.cloneNode(true);
            btnCancel.parentNode.replaceChild(newBtnCancel, btnCancel);
        }
    }
}

function setUpSectionHandlers(section) {
  if (section === 'tasks') {
    // Кнопка создания задачи
    const addTaskBtn = document.getElementById('add-task-btn');
    if (addTaskBtn) addTaskBtn.onclick = function(){ window.openTaskModal('create'); };
    // карточки: редактирование/удаление — если есть другие динамические кнопки, навесьте тут тоже
    if (typeof setUpKanbanButtons === 'function') setUpKanbanButtons();
  }
  if (section === 'projects') {
    // Делегирование на tbody для edit/delete
    const tbody = document.querySelector('.projects .table-block tbody');
    if (tbody) {
      tbody.onclick = e => {
        let btn = e.target.closest('button');
        if (!btn) return;
        const id = +btn.dataset.id;
        if (btn.classList.contains('edit-project-btn')) {
          window.openProjectModal('edit', id);
        }
        if (btn.classList.contains('delete-project-btn')) {
          showModal('warning', 'Удаление проекта', 'Удалить проект безвозвратно?', function() {
            projects = projects.filter(p => p.id !== id);
            renderProjects();
            showModal('success', 'Готово', 'Проект удалён!');
          });
        }
      };
    }
    // Кнопка создания проекта
    const createBtn = document.querySelector('.projects .btn.primary');
    if (createBtn) createBtn.onclick = () => window.openProjectModal('create');
  }
}

document.addEventListener('DOMContentLoaded', function() {
  // --- ТЕКУЩИЙ ПОЛЬЗОВАТЕЛЬ (заглушка: выбираем первого, или можно доработать под auth.js) ---
  let currentUserId = 1; // Иван Иванов по демо-данным
  window.currentUserId = currentUserId;

  // 1. Рендерим все разделы demo-данными
  renderProjects();
  renderTasks();
  renderEmployees();

  // 2. Обработчик на 'Создать задачу'
  const addTaskBtn = document.getElementById('add-task-btn');
  if (addTaskBtn) addTaskBtn.onclick = function(){ window.openTaskModal('create'); };

  // 3. После рендера Kanban снова навешиваем все обработчики на карточки (edit/delete)
  setUpKanbanButtons();

  // --- Навигация по секциям ---
  // --- Новый универсальный способ переключения разделов с динамическим рендером свежих данных ---
  const navBtns = document.querySelectorAll('.sidebar-nav li[data-section]');
  const appContent = document.getElementById('app-content');
  function setActiveSection(section) {
    navBtns.forEach(btn => btn.classList.toggle('active', btn.dataset.section === section));
    // Динамически отрисовываем блок по section
    if (section === 'profile') {
      appContent.innerHTML = `
        <section class="profile section active">
          <h2>Личный кабинет</h2>
          <div class="profile-block">
            <div class="profile-info">
              <div><b>ФИО:</b> Иван Иванов</div>
              <div><b>Почта:</b> client@pm.ru</div>
              <div><b>Биография:</b> Руководитель IT-проекта. Люблю Agile и Kanban.</div>
            </div>
            <div class="profile-actions">
              <button class="btn">Редактировать</button>
              <button class="btn danger">Удалить аккаунт</button>
            </div>
          </div>
        </section>
      `;
    } else if (section === 'employees') {
      appContent.innerHTML = `
        <section class="employees section active">
          <h2>Все сотрудники</h2>
          <div class="table-block">
            <table>
              <thead>
                <tr><th>ФИО</th><th>Почта</th><th>Роль</th></tr>
              </thead>
              <tbody>
              </tbody>
            </table>
          </div>
        </section>
      `;
      renderEmployees();
    } else if (section === 'projects') {
      appContent.innerHTML = `
        <section class="projects section active">
          <h2>Проекты</h2>
          <div class="table-block">
            <table>
              <thead>
                <tr><th>Название</th><th>Тип</th><th>Описание</th><th>Участников</th><th></th></tr>
              </thead>
              <tbody></tbody>
            </table>
            <button class="btn primary" style="margin-top:1.3em;">Создать проект</button>
          </div>
        </section>
      `;
      renderProjects();
    } else if (section === 'tasks') {
      appContent.innerHTML = `
        <section class="tasks section active">
          <h2>Мои задачи</h2>
          <input type="text" id="kanban-search" placeholder="Поиск задач..." class="kanban-search" style="max-width:300px;margin-bottom:1em;">
          <button class="btn primary" id="add-task-btn" style="margin-bottom:1.4em;">Создать задачу</button>
          <div class="kanban-board">
            <div class="kanban-col" data-status="new">
              <div class="kanban-col-title">Новые</div>
              <div class="kanban-cards" id="tasks-new"></div>
            </div>
            <div class="kanban-col" data-status="inprogress">
              <div class="kanban-col-title">В процессе</div>
              <div class="kanban-cards" id="tasks-inprogress"></div>
            </div>
            <div class="kanban-col" data-status="review">
              <div class="kanban-col-title">Можно проверять</div>
              <div class="kanban-cards" id="tasks-review"></div>
            </div>
            <div class="kanban-col" data-status="done">
              <div class="kanban-col-title">Готово</div>
              <div class="kanban-cards" id="tasks-done"></div>
            </div>
          </div>
        </section>
      `;
      renderTasks();
    } else if (section === 'extra') {
      appContent.innerHTML = `
        <section class="other section active">
          <h2>Что-то ещё</h2>
          <p>Этот раздел пока не реализован.</p>
        </section>
      `;
    }
    setUpSectionHandlers(section);
  }
  navBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      setActiveSection(btn.dataset.section);
    });
  });

  // --- Проекты: делегирование edit/delete ---
  const tbody = document.querySelector('.projects .table-block tbody');
  if (tbody) {
      tbody.onclick = e => {
          let btn = e.target.closest('button');
          if (!btn) return;
          const id = +btn.dataset.id;
          if (btn.classList.contains('edit-project-btn')) {
              window.openProjectModal('edit', id);
          }
          if (btn.classList.contains('delete-project-btn')) {
              showModal('warning', 'Удаление проекта', 'Удалить проект безвозвратно?', function() {
                  projects = projects.filter(p => p.id !== id);
                  renderProjects();
                  showModal('success', 'Готово', 'Проект удалён!');
              });
          }
      };
  }

  // --- Кнопка "Создать проект" ---
  const createBtn = document.querySelector('.projects .btn.primary');
  if (createBtn) {
      createBtn.onclick = () => window.openProjectModal('create');
  }

  // --- Кнопка "Отмена" внутри проектной модалки ---
  const cancelBtn = document.getElementById('project-cancel');
  if (cancelBtn) {
      cancelBtn.onclick = window.closeProjectModal;
  }

  // --- Сохранение проекта ---
  const form = document.getElementById('project-form');
  if (form) {
      form.onsubmit = function(e) {
          e.preventDefault();
          const name = form['proj-name'].value.trim();
          const desc = form['proj-desc'].value.trim();
          const type = form['proj-public'].value;
          if (!name || !type) {
              showModal('error', 'Ошибка', 'Название и тип проекта обязательны!');
              return;
          }
          if (editProjectId !== null) {
              projects = projects.map(pr => pr.id === editProjectId ? { ...pr, name, desc, type } : pr);
              showModal('success', 'Успех', 'Проект изменён!');
          } else {
              const newProject = {
                  id: Date.now(),
                  name, desc, type, users: 1
              };
              projects.push(newProject);
              showModal('success', 'Успех', 'Проект добавлен!');
          }
          renderProjects();
          window.closeProjectModal();
          form.reset();
          editProjectId = null;
      };
  }

  // --- Диалог удаления аккаунта ---
  const delBtn = document.querySelector('.profile-actions .danger');
  if (delBtn) {
      delBtn.addEventListener('click', (e) => {
          e.preventDefault();
          showModal(
              'warning',
              'Удалить аккаунт?',
              'После удаления ваш профиль, проекты и задачи будут потеряны. Действие необратимо.',
              () => {
                  showModal('success', 'Аккаунт удалён', 'Ваш аккаунт успешно удалён.', null);
                  // Здесь могла бы быть логика удаления
              }
          );
      });
  }

  // --- Кнопка "Создать задачу" (уже выше) ---

  // --- Логика удаления задачи (при редактировании) ---
  const taskForm = document.getElementById('task-form');
  const taskModal = document.getElementById('modal-task');
  const taskCancelBtn = document.getElementById('task-cancel');
  let taskDeleteBtn = document.createElement('button');
  taskDeleteBtn.type = 'button';
  taskDeleteBtn.className = 'btn mini danger';
  taskDeleteBtn.style = 'float:right;margin-left:1.7em;';
  taskDeleteBtn.innerText = 'Удалить';
  taskDeleteBtn.onclick = function () {
      if (editTaskId) {
          if(confirm('Удалить задачу безвозвратно?')) {
              tasks = tasks.filter(t => t.id !== editTaskId);
              renderTasks();
              window.closeTaskModal();
          }
      }
  };

  // --- submit формы задачи (создание/редактирование) ---
  if (taskForm) {
      taskForm.onsubmit = function(e) {
          e.preventDefault();
          const name = taskForm['task-name'].value.trim();
          const desc = taskForm['task-desc'].value.trim();
          const date = taskForm['task-date'].value;
          const status = taskForm['task-status'].value;
          if (!name) {
              alert('Введите название задачи');
              return;
          }
          if (!date) {
              alert('Укажите дату выполнения');
              return;
          }
          const newTask = {
              id: editTaskId || getRandomId(),
              name,
              desc,
              date,
              status,
              subtasks: taskSubtasks.map(s => ({...s})),
          };
          if (editTaskId) {
              const idx = tasks.findIndex(t=>t.id===editTaskId);
              if(idx!==-1) tasks[idx]=newTask;
          } else {
              tasks.push(newTask);
          }
          renderTasks();
          window.closeTaskModal();
      };
  }

  // --- Кнопка отмены в модалке задачи ---
  if (taskCancelBtn) taskCancelBtn.onclick = () => window.closeTaskModal();

  // --- При открытии модалки задачи — показать/скрыть кнопку удаления ---
  const origOpenTaskModal = window.openTaskModal;
  window.openTaskModal = function(mode, taskId) {
      origOpenTaskModal(mode, taskId);
      let actionsDiv = document.querySelector('#task-form .modal-actions');
      if (mode === 'edit' && taskId) {
          if (!actionsDiv.contains(taskDeleteBtn)) {
              actionsDiv.insertBefore(taskDeleteBtn, actionsDiv.firstChild);
          }
      } else {
          if (actionsDiv.contains(taskDeleteBtn)) actionsDiv.removeChild(taskDeleteBtn);
      }
  };

  // --- Подзадачи: обработка добавления ---
  const addSubtaskBtn = document.getElementById('add-subtask-btn');
  if (addSubtaskBtn) {
      addSubtaskBtn.onclick = ()=>{
          const input = document.getElementById('subtask-input');
          if (!input.value.trim()) return;
          taskSubtasks.push({id: getRandomId(), text: input.value.trim(), done:false});
          input.value = '';
          renderSubtasksUI();
      };
  }

  // --- поиск по задачам ---
  const searchInput = document.getElementById('kanban-search');
  if (searchInput) {
      searchInput.addEventListener('input', (e) => {
          kanbanSearchQuery = e.target.value;
          renderTasks();
      });
  }

  // --- Просмотр задачи: показывает modal-task-view ---
  function openTaskViewModal(taskId) {
    const t = tasks.find(t => t.id === taskId);
    if (!t) return;
    const modal = document.getElementById('modal-task-view');
    const title = document.getElementById('task-view-title');
    const content = document.getElementById('task-view-content');
    if (title) title.textContent = t.name || 'Детали задачи';
    if (content) {
      content.innerHTML = `
        <div style='margin-bottom: .7em;'><b>Столбец:</b> ${getStatusTitle(t.status)}</div>
        <div style='margin-bottom: .7em;'><b>Описание:</b><br>${t.desc || '<i>Нет описания</i>'}</div>
        <div style='margin-bottom: .3em;'><b>Дата:</b> ${t.date ? t.date : '-'}</div>
        ${
          (t.subtasks && t.subtasks.length) ?
          `<div style='margin-top:.7em;'><b>Подзадачи:</b>
            <ul style='margin:4px 0 0 0;padding-left:0;list-style:none;'>
              ${t.subtasks.map(st=>`<li style='display:flex;align-items:center;gap:6px;font-size:.98em;'><input type='checkbox' ${st.done?'checked':''} disabled style='margin:0 4px 0 0;'> <span style='${st.done?'text-decoration:line-through;color:#aaa;':''}'>${st.text}</span></li>`).join('')}
            </ul>
          </div>` : ''
        }
      `;
    }
    modal.style.display = 'flex';
  }
  const btnTaskViewClose = document.getElementById('task-view-close');
  if (btnTaskViewClose) {
    btnTaskViewClose.onclick = () => {
      document.getElementById('modal-task-view').style.display = 'none';
    }
  }
  ['tasks-new','tasks-inprogress','tasks-review','tasks-done'].forEach(colId => {
    const col = document.getElementById(colId);
    if (!col) return;
    col.addEventListener('click', (e) => {
      const card = e.target.closest('.kanban-card');
      if (!card) return;
      if (e.target.closest('button')) return;
      const btn = card.querySelector('.edit-task-btn');
      if (!btn) return;
      const id = +btn.dataset.id;
      openTaskViewModal(id);
    });
  });

  // --- Открытие модалки редактирования профиля ---
  const editProfileBtn = document.querySelector('.profile-actions .btn:not(.danger)');
  const modalProfile = document.getElementById('modal-profile');
  const profileForm = document.getElementById('profile-form');
  const profileCancelBtn = document.getElementById('profile-cancel');

  if (editProfileBtn && modalProfile && profileForm && profileCancelBtn) {
    editProfileBtn.onclick = function() {
      const user = employees.find(e => e.id === currentUserId);
      // автозаполнение
      profileForm['profile-name'].value = user ? user.name : '';
      profileForm['profile-email'].value = user ? user.email : '';
      profileForm['profile-bio'].value = user ? (user.bio || '') : '';
      profileForm['profile-password'].value = '';
      profileForm['profile-password2'].value = '';
      modalProfile.style.display = 'flex';
    }
    profileCancelBtn.onclick = function() {
      modalProfile.style.display = 'none';
      profileForm.reset();
    };
    profileForm.onsubmit = function(e) {
      e.preventDefault();
      const name = profileForm['profile-name'].value.trim();
      const email = profileForm['profile-email'].value.trim();
      const bio = profileForm['profile-bio'].value.trim();
      const pass = profileForm['profile-password'].value;
      const pass2 = profileForm['profile-password2'].value;
      if (pass !== pass2) {
        alert('Пароли не совпадают!');
        return;
      }
      let user = employees.find(e => e.id === currentUserId);
      if (user) {
        user.name = name;
        user.email = email;
        user.bio = bio;
        // pass можно дополнительно обработать (имитация сохранения)
      }
      // Обновим профиль на странице
      const profileEl = document.querySelector('.profile-info');
      if (profileEl) {
        profileEl.innerHTML = `<div><b>ФИО:</b> ${name}</div><div><b>Почта:</b> ${email}</div><div><b>Биография:</b> ${bio}</div>`;
      }
      modalProfile.style.display = 'none';
      profileForm.reset();
    }
  }

  // --- Удаление аккаунта ---
  const deleteProfileBtn = document.querySelector('.profile-actions .btn.danger');
  const modalDelete = document.getElementById('modal-delete-profile');
  const delCancel = document.getElementById('delete-cancel');
  const delConfirm = document.getElementById('delete-confirm');

  if (deleteProfileBtn && modalDelete && delCancel && delConfirm) {
    deleteProfileBtn.onclick = function() {
      modalDelete.style.display = 'flex';
    };
    delCancel.onclick = function() {
      modalDelete.style.display = 'none';
    };
    delConfirm.onclick = function() {
      // Удаляем пользователя из массива
      const idx = employees.findIndex(e => e.id === currentUserId);
      if (idx !== -1) employees.splice(idx, 1);
      modalDelete.style.display = 'none';
      // Показываем заглушку или перезагружаем/заменяем профиль
      const mc = document.querySelector('.main-content');
      if(mc) {
        mc.innerHTML = '<section class="section"><h2>Аккаунт удалён 👋</h2><p>Ваш профиль был удалён из системы.</p></section>';
      }
    };
  }

  // --- Модалка редактирования сотрудника ---
  // (Обработчики открытия/закрытия и сохранения реализованы в renderEmployees)
});

/*
  CSS для анимаций (добавьте в ваш CSS-файл):

  .kanban-fade-in {
    animation: kanbanFadeIn 0.44s cubic-bezier(.4,0,.2,1);
  }
  @keyframes kanbanFadeIn {
    from { opacity: 0; transform: translateY(16px) scale(.97);}
    to   { opacity: 1; transform: none;}
  }
  .kanban-fade-out {
    animation: kanbanFadeOut 0.36s cubic-bezier(.4,0,.2,1);
  }
  @keyframes kanbanFadeOut {
    from { opacity: 1; transform: none;}
    to   { opacity: 0; transform: translateY(16px) scale(.97);}
  }

  // Для структурированной карточки Kanban:
  .kanban-card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 8px #0001;
    margin-bottom: 1.1em;
    padding: 1.1em 1.1em 0.7em 1.1em;
    display: flex;
    flex-direction: column;
    gap: 0.7em;
    border-left: 4px solid #e0e4f0;
    transition: box-shadow .18s;
  }
  .kanban-card-column {
    display: flex;
    flex-direction: column;
    gap: 0.5em;
  }
  .status-dot-wrapper {
    margin-bottom: 0.1em;
  }
  .status-dot {
    display: inline-block;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    box-shadow: 0 0 0 1.5px #b0b6c5;
    vertical-align: -2px;
    margin-right: 0.2em;
  }
  .card-title {
    font-weight: 600;
    font-size: 1.08em;
    color: #2a2d3a;
    margin-right: 0.5em;
    letter-spacing: 0.01em;
    margin-bottom: 0.1em;
  }
  .card-desc {
    color: #5a5a6a;
    font-size: 0.99em;
    margin-bottom: 0.3em;
    margin-top: 0.1em;
    line-height: 1.5;
    padding-left: 1.2em;
    border-left: 2px solid #e8eaf0;
    background: #f8fafd;
    border-radius: 0 6px 6px 0;
    padding-top: 0.2em;
    padding-bottom: 0.2em;
  }
  .kanban-subtasks {
    margin: 0.2em 0 0.5em 0;
    padding-left: 1.2em;
    list-style: none;
  }
  .kanban-subtasks li {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.98em;
    margin-bottom: 0.1em;
  }
  .card-actions {
    display: flex;
    gap: 0.5em;
    justify-content: flex-end;
    margin-top: 0.2em;
    border-top: 1px solid #f0f2f7;
    padding-top: 0.5em;
  }
  .mini-indicator {
    font-size: 0.98em;
    margin-left: 0.2em;
    margin-right: 0.2em;
    vertical-align: middle;
    display: inline-flex;
    align-items: center;
    gap: 0.2em;
  }
*/
