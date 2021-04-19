<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class TaskController extends AbstractController
{
    /**
     * @Route("/tasks",
     *     name="task_list",
     *     methods={"GET"}
     * )
     */
    public function listAction(TaskRepository $taskRepository)
    {
        return $this->render('task/list.html.twig', [
            'tasks' => $taskRepository->findBy(['isDone' => false]),
        ]);
    }

    /**
     * @Route("/tasks/done",
     *     name="task-done_list",
     *     methods={"GET"}
     * )
     */
    public function listDoneAction(TaskRepository $taskRepository)
    {
        return $this->render('task/list.html.twig', [
            'tasks' => $taskRepository->findBy(['isDone' => true]),
        ]);
    }

    /**
     * @Route("/tasks/create",
     *     name="task_create",
     *     methods={"GET", "POST"}
     * )
     */
    public function createAction(Request $request)
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $task->setUser($this->getUser());
            $em = $this->getDoctrine()->getManager();
            $em->persist($task);
            $em->flush();

            $this->addFlash('success', 'La tâche a été bien été ajoutée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/tasks/{id}/edit",
     *     name="task_edit",
     *     methods={"GET", "POST"}
     * )
     */
    public function editAction(Task $task, Request $request)
    {
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'La tâche a bien été modifiée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
    }

    /**
     * @Route("/tasks/{id}/toggle",
     *     name="task_toggle",
     *     methods={"POST"}
     * )
     */
    public function toggleTaskAction(Task $task, Request $request)
    {
        $csrfToken = $request->request->get('_csrf_token');

        if ($this->isCsrfTokenValid('task_toggle', $csrfToken)) {
            $task->toggle(!$task->isDone());
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash(
                'success',
                sprintf('La tâche %s a bien été marquée comme %s.', $task->getTitle(), $task->isDone() ? 'faite': 'non faite')
            );
        }

        return $this->redirectToRoute('task_list');
    }

    /**
     * @Route("/tasks/{id}/delete",
     *     name="task_delete",
     *     methods={"POST"}
     * )
     * @IsGranted("TASK_MANAGE", subject="task")
     */
    public function deleteTaskAction(Task $task, EntityManagerInterface $entityManager, Request $request)
    {
        $csrfToken = $request->request->get('_csrf_token');

        if ($this->isCsrfTokenValid('task_delete', $csrfToken)) {
            $entityManager->remove($task);
            $entityManager->flush();

            $this->addFlash('success', 'La tâche a bien été supprimée.');
        }

        return $this->redirectToRoute('task_list');
    }
}
