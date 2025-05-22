function fetchProjects() {
  const tbody = document.querySelector('#projects-table-body');
  if (tbody) {
    tbody.innerHTML = '<tr><td colspan="4" class="text-center">Загрузка проектов...</td></tr>';

    fetch('controllers/ProjectController.php?action=getAllProjects')
      .then(response => {
        if (!response.ok) {
          throw new Error('Ошибка сервера: ' + response.status);
        }
        return response.json();
      })
      .then(data => {
        if (data.status === 'success') {
          if (data.data.length > 0) {
            let tableHtml = '';
            data.data.forEach(project => {
              tableHtml += `
                <tr>
                  <td>${project.name}</td>
                  <td>${project.is_public ? 'Открытый' : 'Закрытый'}</td>
                  <td>${project.description || '-'}</td>
                  <td>-</td>
                </tr>
              `;
            });
            tbody.innerHTML = tableHtml;
          } else {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center">Проекты не найдены</td></tr>';
          }
        } else {
          tbody.innerHTML = `<tr><td colspan="4" class="text-center">Ошибка: ${data.message}</td></tr>`;
        }
      })
      .catch(error => {
        console.error('Ошибка при загрузке проектов:', error);
        tbody.innerHTML = '<tr><td colspan="4" class="text-center">Не удалось загрузить проекты</td></tr>';
      });
  }
}

function createProject(projectData) {
  return fetch('controllers/ProjectController.php?action=createProject', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(projectData)
  })
  .then(response => response.json());
}

function updateProject(projectData) {
  return fetch('controllers/ProjectController.php?action=updateProject', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(projectData)
  })
  .then(response => response.json());
}

function deleteProject(projectId) {
  return fetch('controllers/ProjectController.php?action=deleteProject', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ id: projectId })
  })
  .then(response => response.json());
}

document.addEventListener('DOMContentLoaded', function() {
  const createProjectForm = document.getElementById('create-project-form');
  if (createProjectForm) {
    createProjectForm.addEventListener('submit', function(e) {
      e.preventDefault();

      const nameInput = this.querySelector('input[name="project_name"]');
      const descriptionInput = this.querySelector('textarea[name="project_description"]');
      const typeSelect = this.querySelector('select[name="project_type"]');

      if (!nameInput.value.trim()) {
        alert('Название проекта обязательно для заполнения');
        return;
      }

      let isPublic = typeSelect.value === 'public';

      const projectData = {
        name: nameInput.value.trim(),
        description: descriptionInput.value.trim(),
        is_public: isPublic
      };

      createProject(projectData)
        .then(data => {
          if (data.status === 'success') {
            document.getElementById('modal-create-project').style.display = 'none';
            document.getElementById('blur-overlay').style.display = 'none';

            nameInput.value = '';
            descriptionInput.value = '';
            typeSelect.selectedIndex = 0;

            if (document.querySelector('.projects.section.active')) {
              fetchProjects();
            }

            alert('Проект успешно создан');
          } else {
            alert('Ошибка: ' + data.message);
          }
        })
        .catch(error => {
          console.error('Ошибка при создании проекта:', error);
          alert('Не удалось создать проект. Попробуйте еще раз.');
        });
    });
  }

  const projectsTab = document.querySelector('.sidebar-btn[data-section="projects"]');
  if (projectsTab) {
    projectsTab.addEventListener('click', function() {
      setTimeout(fetchProjects, 100);
    });
  }
});
