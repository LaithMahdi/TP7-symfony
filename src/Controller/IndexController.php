<?php
namespace App\Controller;
use App\Entity\Article;
use App\Entity\Category;
Use Symfony\Component\Routing\Annotation\Route;
use App\Form\ArticleType;
use App\Form\CategoryType;
use App\Entity\PriceSearch;
use App\Form\PriceSearchType;
use App\Entity\CategorySearch;
use App\Entity\PropertySearch;
use App\Form\CategorySearchType;
use App\Form\PropertySearchType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



class IndexController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
        
    }


    #[Route('/', name: 'article_list')]
    public function home(Request $request)
    {
        // $articles= $this->entityManager->getRepository(Article::class)->findAll();
        // return $this->render('articles/index.html.twig',['articles'=> $articles]);
        $propertySearch = new PropertySearch();
        $form = $this->createForm(PropertySearchType::class,$propertySearch);
        $form->handleRequest($request);
        //initialement le tableau des articles est vide,
        //c.a.d on affiche les articles que lorsque l'utilisateur
        //clique sur le bouton rechercher
        $articles= [];
        if($form->isSubmitted() && $form->isValid()) {
        //on récupère le nom d'article tapé dans le formulaire

        $nom = $propertySearch->getNom();
        if ($nom!="")
        //si on a fourni un nom d'article on affiche tous les articles ayant ce nom
        $articles= $this->entityManager->getRepository(Article::class)->findBy(['Nom' => $nom] );
        else
        //si si aucun nom n'est fourni on affiche tous les articles
        $articles= $this->entityManager->getRepository(Article::class)->findAll();
        }
        return $this->render('articles/index.html.twig',[ 'form' =>$form->createView(), 'articles' => $articles]); 
    }

    #[Route('/articles/create', name: 'new_article',methods: ["POST","GET"])]
    public function new(Request $request,FormFactoryInterface $formFactory)
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class,$article);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $article = $form->getData();
            $this->entityManager->persist($article);
            $this->entityManager->flush();
            return $this->redirectToRoute('article_list');
        }
        return $this->render('articles/new.html.twig',['form' => $form->createView()]);
    }

 
    #[Route('/category/newCat',name:'new_category',methods:["POST","GET"])]
    public function newCategory(Request $request,FormFactoryInterface $formFactory) {
        $category = new Category();
        $form = $this->createForm(CategoryType::class,$category);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
        $article = $form->getData();
        $this->entityManager->persist($category);
        $this->entityManager->flush();
    }
    return $this->render('articles/newCategory.html.twig',['form'=>$form->createView()]);
    }
   

    #[Route('/article/edit/{id}', name: 'edit_article',methods: ["POST","GET"])]
    public function edit(Request $request, $id,FormFactoryInterface $formFactory) {
        $article = new Article();
        $article = $this->entityManager->getRepository(Article::class)->find($id);
        $form = $this->createForm(ArticleType::class,$article);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
        $this->entityManager->flush();
        return $this->redirectToRoute('article_list');
        }
        return $this->render('articles/edit.html.twig', ['form' =>$form->createView()]);
    }
        

  
    #[Route('/article/delete/{id}', name: 'delete_article')]
    public function delete(Request $request, $id) {
        $article = $this->entityManager->getRepository(Article::class)->find($id);
    
        //$entityManager = $this->getDoctrine()->getManager();
        $this->entityManager->remove($article);
        $this->entityManager->flush();
    
        $response = new Response();
        $response->send();
        return $this->redirectToRoute('article_list');
    }
   
    
    #[Route('/article/{id}', name: 'article_show')]
    public function show($id) {
        $article = $this->entityManager->getRepository(Article::class)->find($id);
        return $this->render('articles/show.html.twig',array('article' => $article));
    }
    

    #[Route('/art_cat/', name: 'article_par_cat',methods: ["POST","GET"])]
    public function articlesParCategorie(Request $request) {
        $categorySearch = new CategorySearch();
        $form = $this->createForm(CategorySearchType::class,$categorySearch);
        $form->handleRequest($request);
        $articles= [];
        if($form->isSubmitted() && $form->isValid()) {
            $category = $categorySearch->getCategory();
        if ($category!="")
            $articles= $category->getArticles();
        else
            $articles= $this->entityManager->getRepository(Article::class)->findAll();
        }
    
        return $this->render('articles/articlesParCategorie.html.twig',['form' => $form->createView(),'articles' => $articles]);
    }
   

    #[Route('/art_prix/', name: 'article_par_prix',methods: ["GET","POST"])]
    public function articlesParPrix(Request $request)
    {
        $priceSearch = new PriceSearch();
        $form = $this->createForm(PriceSearchType::class,$priceSearch);
        $form->handleRequest($request);
        $articles= [];
        if($form->isSubmitted() && $form->isValid()) {
            $minPrice = $priceSearch->getMinPrice();
            $maxPrice = $priceSearch->getMaxPrice();
            $articles=  $this->entityManager->getRepository(Article::class)->findByPriceRange($minPrice,$maxPrice);
        }
        return $this->render('articles/articlesParPrix.html.twig',[ 'form' =>$form->createView(), 'articles' => $articles]);
    }



 
}

?>