// Универсальная функция для модальных окон (шорткат)
function showSimpleModal(type, title, message) {
    if (typeof showModal === 'function') {
      showModal(type, title, message);
    } else {
      alert(title + '\n' + message); // fallback
    }
  }
  
  // Авторизация
  const loginForm = document.querySelector('.auth-form');
  if (loginForm && window.location.pathname.includes('index.html')) {
    loginForm.addEventListener('submit', function(e) {
      e.preventDefault();
      const login = loginForm.querySelector('input[name="login"]').value.trim();
      const pass = loginForm.querySelector('input[name="password"]').value.trim();
      if (login === 'client' && pass === 'clientOlimp') {
        window.location.href = 'main.html';
      } else {
        showSimpleModal('error', 'Ошибка авторизации', 'Логин или пароль введены неверно.');
      }
    });
  }
  
  // Регистрация с валидацией
  if (loginForm && window.location.pathname.includes('registration.html')) {
    loginForm.addEventListener('submit', function(e) {
      e.preventDefault();
      const name = loginForm.querySelector('input[name="register-name"]').value.trim();
      const login = loginForm.querySelector('input[name="register-login"]').value.trim();
      const email = loginForm.querySelector('input[name="register-email"]').value.trim();
      const pass = loginForm.querySelector('input[name="register-password"]').value;
      const pass2 = loginForm.querySelector('input[name="register-password-repeat"]').value;
      const lang = loginForm.querySelector('select[name="register-language"]').value;
  
      // Проверки
      if (!name || !login || !email || !pass || !pass2 || !lang) {
        showSimpleModal('error', 'Ошибка', 'Заполните все обязательные поля.');
        return;
      }
      // ФИО: только латиница и пробелы
      if (!/^([A-Za-z ]+)$/.test(name)) {
        showSimpleModal('error', 'Ошибка в ФИО', 'ФИО может содержать только латинские буквы и пробелы.');
        return;
      }
      // Email
      if (!/^\S+@\S+\.\S+$/.test(email)) {
        showSimpleModal('error', 'Ошибка в Email', 'Неверный формат email.');
        return;
      }
      // Пароли совпадают
      if (pass !== pass2) {
        showSimpleModal('error', 'Ошибка пароля', 'Пароли не совпадают.');
        return;
      }
      // Длина пароля
      if (pass.length < 6) {
        showSimpleModal('error', 'Ошибка пароля', 'Пароль должен содержать минимум 6 символов.');
        return;
      }
      showSimpleModal('success', 'Регистрация успешна', 'Вы успешно зарегистрировались!');
      setTimeout(() => {
        window.location.href = 'index.html';
      }, 900);
    });
  }
  