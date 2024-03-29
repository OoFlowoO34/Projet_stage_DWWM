<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/user')]
class UserController extends AbstractController
{


    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, Filesystem $filesystem, FileUploader $fileUploader): Response
    {

        //cast the variables to integer 
        $userId = (int) $this->getUser()->getId();
        $requestId = (int) $request->get('id');
        
        if( $userId == $requestId){

            $form = $this->createForm(UserType::class, $user);
            $form->handleRequest($request);

            $pd = $form->get('Password')->getData();

            if ($form->isSubmitted() && $form->isValid() && $userPasswordHasher->isPasswordValid($user, $pd)) {
               
                if($form->get('plainPassword')->getData()){
                $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                    )
                );
                }

            /*xxxxxxxxxxxxxxx
            x    PHOTO      x
            xxxxxxxxxxxxxxxx*/

            /** @var UploadedFile $photoFile */
            $photoFile = $form->get('photo')->getData();

            /*__________________________________________________________________________
            This condition is needed because the 'photo' field is not required
            so the photo file must be processed only when a file is uploaded         ↓ */
            if ($photoFile) {
                
                // Get the route and the filename to delete
                $photo = $user->getPhoto();
                $TargetDirectory = $fileUploader->getTargetDirectory();
                $photo_pointer = $TargetDirectory.'/'.$photo;

                // Delete the photo to be replaced, like unlink($photo_pointer);
                if($photo){
                    $filesystem->remove($photo_pointer);
                }
                

                // To avoid logic in controllers, making them big, I extracted the upload logic to a separate service ( fileUploader ).
                // Store the photo and return a new uniq name.
                $photoFileName = $fileUploader->upload($photoFile);
                
                // Store the photo file name instead of its contents
                $user->setPhoto($photoFileName);
            }


                $entityManager->persist($user);
                $entityManager->flush();

                // $userRepository->save($user, true);

                return $this->redirectToRoute('app_user_edit', array('id' => $userId), Response::HTTP_SEE_OTHER);
            }

            return $this->renderForm('user/edit.html.twig', [
                'user' => $user,
                'form' => $form,
            ]);
        }
        else{
             return $this->redirectToRoute('app_user_edit',array('id' => $userId));
        }
        
    }

















        // #[Route('/', name: 'app_user_index', methods: ['GET'])]
    // public function index(UserRepository $userRepository): Response
    // {
    //     return $this->render('user/index.html.twig', [
    //         'users' => $userRepository->findAll(),
    //     ]);
    // }

    // #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    // public function new(Request $request, UserRepository $userRepository): Response
    // {
    //     $user = new User();
    //     $form = $this->createForm(UserType::class, $user);
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $userRepository->save($user, true);

    //         return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    //     }

    //     return $this->renderForm('user/new.html.twig', [
    //         'user' => $user,
    //         'form' => $form,
    //     ]);
    // }

    // #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    // public function show(User $user): Response
    // {
    //     return $this->render('user/show.html.twig', [
    //         'user' => $user,
    //     ]);
    // }

    
    // #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    // public function delete(Request $request, User $user, UserRepository $userRepository): Response
    // {
    //     if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
    //         $userRepository->remove($user,  v);
    //     }

    //     return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    // }
}
