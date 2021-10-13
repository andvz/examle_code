<?php declare(strict_types=1);

namespace App\Controller\Admin\Catalog\Purpose;

use App\Entity\Catalog\CatalogCategory;
use App\Entity\Catalog\Purpose;
use App\Repository\Catalog\PurposeCatalogCategoryRepository;
use App\Repository\Catalog\PurposeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/catalog/purpose", name="catalog_purpose_")
 */
class PurposeController extends AbstractController
{
    private EntityManagerInterface $em;
    private PurposeCatalogCategoryRepository $purposeCatalogCategoryRepository;

    public function __construct(EntityManagerInterface $em, PurposeCatalogCategoryRepository $purposeCatalogCategoryRepository)
    {
        $this->em = $em;
        $this->purposeCatalogCategoryRepository = $purposeCatalogCategoryRepository;
    }

    /**
     * @Route(name="list")
     */
    public function list(PurposeRepository $purposeRepository, Request $request): Response
    {
        $filterForm = $this->createForm(PurposeFilterForm::class);
        $filterForm->handleRequest($request);
        $criteria = [];

        if ($filterForm->isSubmitted() && $filterForm->isValid()) {
            $filterData = $filterForm->getData();
            if ($filterData['title']) {
                $criteria = ['title' => $filterData['title']];
            }
        }

        $purposes = $purposeRepository->findBy($criteria, ['title' => 'ASC']);

        return $this->render('admin/catalog/purpose/list.html.twig', [
            'purposes' => $purposes,
            'filter_form' => $filterForm->createView(),
        ]);
    }

    /**
     * @Route("/create", name="create")
     */
    public function create(Request $request): Response
    {
        $purpose = new Purpose();
        $form = $this->createForm(PurposeForm::class, $purpose);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($purpose);

            /**
             * @var CatalogCategory[] $catalogCategories
             */
            $catalogCategories = $form->get('catalogCategories')->getData();
            $purpose->addCatalogCategories($catalogCategories);

            $this->em->flush();
            $this->addFlash('success', 'Назначение создано');

            return $this->redirectToRoute('admin_catalog_purpose_update', ['id' => $purpose->getId()]);
        }

        return $this->render('admin/catalog/purpose/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/update/{id}", name="update")
     */
    public function update(Purpose $purpose, Request $request): Response
    {
        $form = $this->createForm(PurposeForm::class, $purpose);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var CatalogCategory[] $catalogCategories
             */
            $catalogCategories = $form->get('catalogCategories')->getData();

            $purposeCatalogCategories = $this->purposeCatalogCategoryRepository->findOrCreate($purpose, $catalogCategories);
            $purpose->replacePurposeCatalogCategories($purposeCatalogCategories);

            $this->em->flush();
            $this->addFlash('success', 'Назначение обновлено');

            return $this->redirect($request->getUri());
        }

        return $this->render('admin/catalog/purpose/update.html.twig', [
            'form' => $form->createView(),
            'purpose' => $purpose,
        ]);
    }

    /**
     * @Route("/delete/{id}", name="delete")
     */
    public function delete(Purpose $purpose): RedirectResponse
    {
        $this->em->remove($purpose);
        $this->em->flush();
        $this->addFlash('success', 'Назначение удалено');

        return $this->redirectToRoute('admin_catalog_purpose_list');
    }
}
