<?php
    require_once __DIR__.'/../classes/ProjectContext.php';

    class ProjectController
    {
        public function getAllProjects(): void
        {
            header('Content-Type: application/json');

            try {
                $projects = ProjectContext::select();

                $projectsArray = array_map(function ($project) {
                    return [
                        'id' => $project->Id,
                        'name' => $project->Name,
                        'description' => $project->Description,
                        'is_public' => $project->Is_Public
                    ];
                }, $projects);

                echo json_encode([
                    'status' => 'success',
                    'data' => $projectsArray
                ]);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Не удалось получить проекты: ' . $e->getMessage()
                ]);
            }
        }

        public function createProject(): void
        {
            header('Content-Type: application/json');

            try {
                $postData = json_decode(file_get_contents('php://input'), true);

                if (!isset($postData['name']) || empty($postData['name'])) {
                    throw new Exception('Название проекта обязательно для заполнения');
                }

                $projectData = [
                    'id' => 0,
                    'name' => $postData['name'],
                    'description' => $postData['description'] ?? null,
                    'is_public' => isset($postData['is_public']) ? (bool)$postData['is_public'] : false
                ];

                $project = new ProjectContext($projectData);
                $project->add();

                echo json_encode([
                    'status' => 'success',
                    'message' => 'Проект успешно создан'
                ]);
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Не удалось создать проект: ' . $e->getMessage()
                ]);
            }
        }

        public function updateProject(): void
        {
            header('Content-Type: application/json');

            try {
                $postData = json_decode(file_get_contents('php://input'), true);

                if (!isset($postData['id']) || empty($postData['id'])) {
                    throw new Exception('ID проекта обязателен для обновления');
                }

                if (!isset($postData['name']) || empty($postData['name'])) {
                    throw new Exception('Название проекта обязательно для заполнения');
                }

                $projectData = [
                    'id' => $postData['id'],
                    'name' => $postData['name'],
                    'description' => $postData['description'] ?? null,
                    'is_public' => isset($postData['is_public']) ? (bool)$postData['is_public'] : false
                ];

                $project = new ProjectContext($projectData);
                $project->update();

                echo json_encode([
                    'status' => 'success',
                    'message' => 'Проект успешно обновлен'
                ]);
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Не удалось обновить проект: ' . $e->getMessage()
                ]);
            }
        }

        public function deleteProject(): void
        {
            header('Content-Type: application/json');

            try {
                $postData = json_decode(file_get_contents('php://input'), true);

                if (!isset($postData['id']) || empty($postData['id'])) {
                    throw new Exception('ID проекта обязателен для удаления');
                }

                $projectData = [
                    'id' => $postData['id'],
                    'name' => '', 
                    'description' => null,
                    'is_public' => false
                ];

                $project = new ProjectContext($projectData);
                $project->delete();

                echo json_encode([
                    'status' => 'success',
                    'message' => 'Проект успешно удален'
                ]);
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Не удалось удалить проект: ' . $e->getMessage()
                ]);
            }
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
        $controller = new ProjectController();

        if ($_GET['action'] === 'getAllProjects') {
            $controller->getAllProjects();
        }
    } else if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action'])) {
        $controller = new ProjectController();

        switch ($_GET['action']) {
            case 'createProject':
                $controller->createProject();
                break;
            case 'updateProject':
                $controller->updateProject();
                break;
            case 'deleteProject':
                $controller->deleteProject();
                break;
            default:
                header('Content-Type: application/json');
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Неизвестное действие'
                ]);
        }
    }
?>
