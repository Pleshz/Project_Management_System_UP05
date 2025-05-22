function getProjectBasePath() {
  const path = window.location.pathname;
  const lastSlash = path.lastIndexOf('/');
  return path.substring(0, lastSlash + 1);
}

function registerUser(userData) {
  const basePath = getProjectBasePath();

  return fetch(`${basePath}controllers/UserController.php?action=register`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(userData)
  })
  .then(response => {
    if (!response.ok) {
      return response.text().then(text => {
        console.error('Ответ сервера:', text);
        throw new Error('Ошибка сервера: ' + response.status);
      });
    }
    return response.json();
  });
}

function loginUser(loginData) {
  const basePath = getProjectBasePath();

  return fetch(`${basePath}controllers/UserController.php?action=login`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(loginData)
  })
  .then(response => {
    if (!response.ok) {
      return response.text().then(text => {
        console.error('Ответ сервера:', text);
        throw new Error('Ошибка сервера: ' + response.status);
      });
    }
    return response.json();
  });
}

function logoutUser() {
  const basePath = getProjectBasePath();

  return fetch(`${basePath}controllers/UserController.php?action=logout`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    }
  })
  .then(response => {
    if (!response.ok) {
      return response.text().then(text => {
        console.error('Ответ сервера:', text);
        throw new Error('Ошибка сервера: ' + response.status);
      });
    }
    return response.json();
  });
}

function getCurrentUser() {
  const basePath = getProjectBasePath();

  return fetch(`${basePath}controllers/UserController.php?action=getCurrentUser`)
  .then(response => {
    if (!response.ok) {
      if (response.status === 401) {
        window.location.href = `${basePath}index.html`;
        throw new Error('Пользователь не авторизован');
      }
      return response.text().then(text => {
        console.error('Ответ сервера:', text);
        throw new Error('Ошибка сервера: ' + response.status);
      });
    }
    return response.json();
  });
}

function updateUser(userData) {
  const basePath = getProjectBasePath();

  return fetch(`${basePath}controllers/UserController.php?action=updateUser`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(userData)
  })
  .then(response => {
    if (!response.ok) {
      return response.text().then(text => {
        console.error('Ответ сервера:', text);
        throw new Error('Ошибка сервера: ' + response.status);
      });
    }
    return response.json();
  });
}

function deleteUser(password) {
  const basePath = getProjectBasePath();

  return fetch(`${basePath}controllers/UserController.php?action=deleteUser`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ password })
  })
  .then(response => {
    if (!response.ok) {
      return response.text().then(text => {
        console.error('Ответ сервера:', text);
        throw new Error('Ошибка сервера: ' + response.status);
      });
    }
    return response.json();
  });
}

function displayUserProfile(userData) {
  const profileAvatar = document.querySelector('.profile-avatar');
  const profileUsername = document.querySelector('.profile-main-username');
  const profileEmail = document.querySelector('.profile-main-email');
  const profilePhone = document.querySelector('.profile-main-meta.phone-meta');
  const profileBioText = document.querySelector('.profile-bio-text');

  const profileFullName = document.querySelector('.profile-edit-form input[type="text"]');
  const profileLoginInput = document.querySelector('.profile-edit-form .login-field');
  const profileEmailInput = document.querySelector('.profile-edit-form input[type="email"]');
  const profilePhoneInput = document.querySelector('.profile-edit-form input[type="tel"]');
  const profileBioInput = document.querySelector('.profile-edit-form .bio-field');

  // Обновляем отображаемую информацию в левой колонке
  if (profileUsername) profileUsername.textContent = userData.login;
  if (profileEmail) profileEmail.textContent = userData.email;
  if (profilePhone) profilePhone.textContent = userData.phone || '+7 999 888-77-66';
  if (profileBioText) profileBioText.textContent = userData.bio || 'Информация не указана';

  // Обновляем поля формы редактирования
  if (profileFullName) profileFullName.value = userData.full_name || '';
  if (profileLoginInput) profileLoginInput.value = userData.login || '';
  if (profileEmailInput) profileEmailInput.value = userData.email || '';
  if (profilePhoneInput) profilePhoneInput.value = userData.phone || '';
  if (profileBioInput) profileBioInput.value = userData.bio || '';

  localStorage.setItem('user_id', userData.id);
  localStorage.setItem('user_login', userData.login);
}

document.addEventListener('DOMContentLoaded', function() {
  const currentPage = window.location.pathname.split('/').pop();

  if (currentPage === 'index.html' || currentPage === '') {
    const loginForm = document.querySelector('.auth-form');
    if (loginForm) {
      loginForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const loginInput = document.getElementById('login');
        const passwordInput = document.getElementById('password');

        if (!loginInput.value.trim() || !passwordInput.value.trim()) {
          alert('Заполните все поля');
          return;
        }

        const loginData = {
          login: loginInput.value.trim(),
          password: passwordInput.value
        };

        loginUser(loginData)
          .then(data => {
            if (data.status === 'success') {
              localStorage.setItem('user_id', data.user.id);
              localStorage.setItem('user_login', data.user.login);

              window.location.href = 'main.html';
            } else {
              alert('Ошибка: ' + data.message);
            }
          })
          .catch(error => {
            console.error('Ошибка при входе:', error);
            alert('Не удалось авторизоваться. Проверьте введенные данные.');
          });
      });
    }
  } else if (currentPage === 'registration.html') {
    const registerForm = document.querySelector('.auth-form');
    if (registerForm) {
      registerForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const loginInput = document.getElementById('register-login');
        const emailInput = document.getElementById('register-email');
        const passwordInput = document.getElementById('register-password');
        const passwordRepeatInput = document.getElementById('register-password-repeat');
        const languageSelect = document.getElementById('register-language');

        if (!loginInput.value.trim() || !emailInput.value.trim() ||
            !passwordInput.value.trim() || !languageSelect.value) {
          alert('Заполните все обязательные поля');
          return;
        }

        if (passwordInput.value !== passwordRepeatInput.value) {
          alert('Пароли не совпадают');
          return;
        }

        const userData = {
          login: loginInput.value.trim(),
          email: emailInput.value.trim(),
          password: passwordInput.value,
          language: languageSelect.value
        };

        registerUser(userData)
          .then(data => {
            if (data.status === 'success') {
              alert('Регистрация успешна! Теперь вы можете авторизоваться.');
              window.location.href = 'index.html';
            } else {
              alert('Ошибка: ' + data.message);
            }
          })
          .catch(error => {
            console.error('Ошибка при регистрации:', error);
            alert('Не удалось зарегистрироваться. Попробуйте еще раз.');
          });
      });
    }
  } else if (currentPage === 'main.html') {
    getCurrentUser()
      .then(data => {
        if (data.status === 'success') {
          displayUserProfile(data.user);
        }
      })
      .catch(error => {
        console.error('Ошибка при получении данных пользователя:', error);
      });

    setTimeout(function() {
      const saveProfileBtn = document.querySelector('.edit-save');
      if (saveProfileBtn) {
        saveProfileBtn.addEventListener('click', function(e) {
          e.preventDefault();

          const fullNameInput = document.querySelector('.profile-edit-form input[value]');
          const loginInput = document.querySelector('.profile-edit-form .login-field');
          const emailInput = document.querySelector('.profile-edit-form input[type="email"]');
          const phoneInput = document.querySelector('.profile-edit-form input[type="tel"]');
          const bioInput = document.querySelector('.profile-edit-form .bio-field');

          const userData = {
            full_name: fullNameInput ? fullNameInput.value.trim() : '',
            login: loginInput ? loginInput.value.trim() : '',
            email: emailInput ? emailInput.value.trim() : '',
            phone: phoneInput ? phoneInput.value.trim() : '',
            bio: bioInput ? bioInput.value.trim() : '',
          };

          updateUser(userData)
            .then(data => {
              if (data.status === 'success') {
                alert('Данные успешно обновлены');
                displayUserProfile(data.user);
              } else {
                alert('Ошибка: ' + data.message);
              }
            })
            .catch(error => {
              console.error('Ошибка при обновлении данных:', error);
              alert('Не удалось обновить данные. Попробуйте еще раз.');
            });
        });
      }

      const logoutBtn = document.querySelector('.edit-logout');
      if (logoutBtn) {
        logoutBtn.addEventListener('click', function() {
          logoutUser()
            .then(data => {
              if (data.status === 'success') {
                localStorage.removeItem('user_id');
                localStorage.removeItem('user_login');

                window.location.href = 'index.html';
              } else {
                alert('Ошибка: ' + data.message);
              }
            })
            .catch(error => {
              console.error('Ошибка при выходе:', error);
              alert('Не удалось выйти из аккаунта. Попробуйте еще раз.');
            });
        });
      }

      const deleteAccountBtn = document.querySelector('.edit-delete');
      if (deleteAccountBtn) {
        deleteAccountBtn.addEventListener('click', function() {
          const password = prompt('Для удаления аккаунта введите свой пароль:');

          if (password) {
            if (confirm('Вы уверены, что хотите удалить аккаунт? Это действие нельзя отменить.')) {
              deleteUser(password)
                .then(data => {
                  if (data.status === 'success') {
                    alert('Аккаунт успешно удален');

                    localStorage.removeItem('user_id');
                    localStorage.removeItem('user_login');

                    window.location.href = 'index.html';
                  } else {
                    alert('Ошибка: ' + data.message);
                  }
                })
                .catch(error => {
                  console.error('Ошибка при удалении аккаунта:', error);
                  alert('Не удалось удалить аккаунт. Попробуйте еще раз.');
                });
            }
          }
        });
      }
    }, 100);
  }
});
