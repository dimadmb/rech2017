<?php

namespace AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use BaseBundle\Entity\Image;

class ImageController extends Controller
{
    /**
     * @Route("/image_upload", name="image_upload")
     */
    public function uploadAction(Request $request)
    {
		$em = $this->getDoctrine()->getManager();
		$images = [];
		$imagesF = $request->files->get("files");
		$dump = [];
		
		foreach($imagesF as $file)
		{
			$image = new Image();
			$image->setFile($file);
			$image->setSort(500);
			$em->persist($image);
			$em->flush();
			
			$images[] = $image;
		}

        return $this->render('image/upload.html.twig', array(
            'images' => $images,
			'dump' => $dump,
        ));
    }
	
	
	/**
     * @Route("/image_delete/{id}", name="image_delete")
     */
    public function deleteAction(Request $request, $id = null)
	{
		$em = $this->getDoctrine()->getManager();
		
		$image = $em->getRepository('BaseBundle:Image')->findOneById($id);
		
		if($image != null)
		{
			@unlink($this->getParameter('upload_directory').'/'.$image->getFilename());
			
			$em->remove($image);
			$em->flush();
			
			
			
			return new Response("ok ".$id);
		}
		else 
		{
			return new Response("does not exist ".$id);
		}

		
		
	}	
	
}
