-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Май 21 2025 г., 11:38
-- Версия сервера: 5.7.39-log
-- Версия PHP: 8.1.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `Project_Management_System`
--

-- --------------------------------------------------------

--
-- Структура таблицы `Columns`
--

CREATE TABLE `Columns` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `project_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `Projects`
--

CREATE TABLE `Projects` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `description` text,
  `is_public` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `ProjectUsers`
--

CREATE TABLE `ProjectUsers` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` enum('admin','member','guest') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `Subtasks`
--

CREATE TABLE `Subtasks` (
  `id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `description` text,
  `due_date` date DEFAULT NULL,
  `column_id` int(11) NOT NULL,
  `assignee_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `TaskAssignees`
--

CREATE TABLE `TaskAssignees` (
  `id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `Tasks`
--

CREATE TABLE `Tasks` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `description` text,
  `due_date` date DEFAULT NULL,
  `column_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `Users`
--

CREATE TABLE `Users` (
  `id` int(11) NOT NULL,
  `login` varchar(45) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(120) NOT NULL,
  `full_name` varchar(120) DEFAULT NULL,
  `bio` text,
  `language` enum('en','ru') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `Columns`
--
ALTER TABLE `Columns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`);

--
-- Индексы таблицы `Projects`
--
ALTER TABLE `Projects`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `ProjectUsers`
--
ALTER TABLE `ProjectUsers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `project_id` (`project_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `Subtasks`
--
ALTER TABLE `Subtasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_id` (`task_id`),
  ADD KEY `column_id` (`column_id`),
  ADD KEY `assignee_id` (`assignee_id`);

--
-- Индексы таблицы `TaskAssignees`
--
ALTER TABLE `TaskAssignees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `task_id` (`task_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `Tasks`
--
ALTER TABLE `Tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `column_id` (`column_id`);

--
-- Индексы таблицы `Users`
--
ALTER TABLE `Users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `login` (`login`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `Columns`
--
ALTER TABLE `Columns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `Projects`
--
ALTER TABLE `Projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `ProjectUsers`
--
ALTER TABLE `ProjectUsers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `Subtasks`
--
ALTER TABLE `Subtasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `TaskAssignees`
--
ALTER TABLE `TaskAssignees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `Tasks`
--
ALTER TABLE `Tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `Users`
--
ALTER TABLE `Users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `Columns`
--
ALTER TABLE `Columns`
  ADD CONSTRAINT `columns_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `Projects` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `ProjectUsers`
--
ALTER TABLE `ProjectUsers`
  ADD CONSTRAINT `projectusers_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `Projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `projectusers_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `Subtasks`
--
ALTER TABLE `Subtasks`
  ADD CONSTRAINT `subtasks_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `Tasks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subtasks_ibfk_2` FOREIGN KEY (`column_id`) REFERENCES `Columns` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subtasks_ibfk_3` FOREIGN KEY (`assignee_id`) REFERENCES `Users` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `TaskAssignees`
--
ALTER TABLE `TaskAssignees`
  ADD CONSTRAINT `taskassignees_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `Tasks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `taskassignees_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `Tasks`
--
ALTER TABLE `Tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`column_id`) REFERENCES `Columns` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
