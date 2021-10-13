<?php declare(strict_types=1);

namespace App\Controller\Admin\Catalog\Manufacturer;

use App\Entity\Catalog\CatalogCategory;
use App\Entity\Catalog\Manufacturer;
use App\Repository\Catalog\ManufacturerCatalogCategoryRepository;
use App\Repository\Catalog\ManufacturerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/catalog/manufacturer", name="catalog_manufacturer_")
 */
class ManufacturerController extends AbstractController
{
    private EntityManagerInterface $em;
    private ManufacturerCatalogCategoryRepository $manufacturerCatalogCategoryRepository;

    public function __construct(EntityManagerInterface $em, ManufacturerCatalogCategoryRepository $manufacturerCatalogCategoryRepository)
    {
        $this->em = $em;
        $this->manufacturerCatalogCategoryRepository = $manufacturerCatalogCategoryRepository;
    }

    /**
     * @Route(name="list")
     */
    public function list(ManufacturerRepository $manufacturerRepository, Request $request): Response
    {
        $filterForm = $this->createForm(ManufacturerFilterForm::class);
        $filterForm->handleRequest($request);
        $criteria = [];

        if ($filterForm->isSubmitted() && $filterForm->isValid()) {
            $filterData = $filterForm->getData();
            if ($filterData['title']) {
                $criteria = ['title' => $filterData['title']];
            }
        }

        $manufacturers = $manufacturerRepository->findBy($criteria, ['title' => 'ASC']);

        return $this->render('admin/catalog/manufacturer/list.html.twig', [
            'manufacturers' => $manufacturers,
            'filter_form' => $filterForm->createView(),
        ]);
    }

    /**
     * @Route("/create", name="create")
     */
    public function create(Request $request): Response
    {
        $manufacturer = new Manufacturer();
        $form = $this->createForm(ManufacturerForm::class, $manufacturer);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($manufacturer);

            /**
             * @var CatalogCategory[] $catalogCategories
             */
            $catalogCategories = $form->get('catalogCategories')->getData();
            $manufacturer->addCatalogCategories($catalogCategories);

            $this->em->flush();
            $this->addFlash('success', 'Производитель создан');

            return $this->redirectToRoute('admin_catalog_manufacturer_update', ['id' => $manufacturer->getId()]);
        }

        return $this->render('admin/catalog/manufacturer/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/update/{id}", name="update")
     */
    public function update(Manufacturer $manufacturer, Request $request): Response
    {
        $form = $this->createForm(ManufacturerForm::class, $manufacturer);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var CatalogCategory[] $catalogCategories
             */
            $catalogCategories = $form->get('catalogCategories')->getData();

            $newManufacturerCatalogCategories = $this->manufacturerCatalogCategoryRepository->findOrCreate($manufacturer, $catalogCategories);
            $manufacturer->replaceManufacturerCatalogCategories($newManufacturerCatalogCategories);

            $this->em->flush();
            $this->addFlash('success', 'Производитель обновлен');

            return $this->redirect($request->getUri());
        }

        return $this->render('admin/catalog/manufacturer/update.html.twig', [
            'form' => $form->createView(),
            'manufacturer' => $manufacturer,
        ]);
    }

    /**
     * @Route("/delete/{id}", name="delete")
     */
    public function delete(Manufacturer $manufacturer): RedirectResponse
    {
        $this->em->remove($manufacturer);
        $this->em->flush();
        $this->addFlash('success', 'Производитель удален');

        return $this->redirectToRoute('admin_catalog_manufacturer_list');
    }
}
