<?php declare(strict_types=1);

namespace App\Controller\Site\Catalog;

use App\Entity\Catalog\CatalogCategory;
use App\Entity\Catalog\Purpose;
use App\Entity\Catalog\Manufacturer;
use App\Repository\Catalog\PurposeRepository;
use App\Repository\Catalog\ManufacturerRepository;
use App\Repository\Catalog\ProductRepository;
use App\Service\Catalog\CatalogCategoriesHandler;
use App\Service\Catalog\CatalogSeoHandler;
use App\Service\Catalog\Product\ProductCollectionDataTransformer;
use App\Service\Catalog\Product\ProductRelationsHandler;
use App\Service\Pagination;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class CatalogCategoryController extends AbstractController
{
    public const PRICE_MAX = 999999;
    private ProductRepository $productRepository;
    private ProductRelationsHandler $productRelationsHandler;
    private ManufacturerRepository $manufacturerRepository;
    private PurposeRepository $purposeRepository;
    private ProductCollectionDataTransformer $productCollectionDataTransformer;
    private Pagination $pagination;
    private CatalogSeoHandler $catalogSeoHandler;
    private CatalogCategoriesHandler $catalogCategoriesHandler;

    public function __construct(
        ProductRepository $productRepository,
        ProductRelationsHandler $productRelationsHandler,
        ManufacturerRepository $manufacturerRepository,
        PurposeRepository $purposeRepository,
        ProductCollectionDataTransformer $productCollectionDataTransformer,
        Pagination $pagination,
        CatalogSeoHandler $catalogSeoHandler,
        CatalogCategoriesHandler $catalogCategoriesHandler
    ) {
        $this->productRepository = $productRepository;
        $this->productRelationsHandler = $productRelationsHandler;
        $this->manufacturerRepository = $manufacturerRepository;
        $this->purposeRepository = $purposeRepository;
        $this->productCollectionDataTransformer = $productCollectionDataTransformer;
        $this->pagination = $pagination;
        $this->catalogSeoHandler = $catalogSeoHandler;
        $this->catalogCategoriesHandler = $catalogCategoriesHandler;
    }

    /**
     * @Route("/category/{alias}", name="catalog_category_view")
     */
    public function categoryView(Request $request, CatalogCategory $catalogCategory): Response
    {
        $selectedParameters = $this->preparationSelectedParameter($request->get('parameter_values', []));

        $sort = $request->get('sort', null);
        $priceMin = (int) $request->get('priceMin', 0);
        $priceMax = (int) $request->get('priceMax', self::PRICE_MAX);

        $catalogCategoriesIds = $this->catalogCategoriesHandler->getChildrenCategoriesIds($catalogCategory);

        $products = $this->pagination->changePageSize(21)->paginate(
            $this->productRepository->findProductsByCatalog($catalogCategoriesIds, $selectedParameters),
            (int) $request->get('page', 1)
        )->getPaginator();

        $seo = $this->catalogSeoHandler->getCategorySeo($catalogCategory);

        if (null !== $seo) {
            $seo = $this->catalogSeoHandler->getCatalogSeo($seo, null, null);
        }

        return $this->createResponse(
            $catalogCategory,
            $products,
            $selectedParameters,
            $seo,
            null,
            null,
            $sort,
            $priceMin,
            $priceMax
        );
    }

    /**
     * @Route("/category/{category_alias}/manufacturer/{manufacturer_alias}", name="catalog_category_manufacturer_view")
     * @ParamConverter("catalogCategory", options={"mapping": {"category_alias": "alias"}}))
     * @ParamConverter("manufacturer", options={"mapping": {"manufacturer_alias": "alias"}}))
     */
    public function categoryManufacturerView(
        Request $request,
        CatalogCategory $catalogCategory,
        Manufacturer $manufacturer
    ): Response {
        $selectedParameters = $this->preparationSelectedParameter($request->get('parameter_values', []));
        $sort = $request->get('sort', null);
        $priceMin = (int) $request->get('priceMin', 0);
        $priceMax = (int) $request->get('priceMax', self::PRICE_MAX);

        $catalogCategoriesIds = $this->catalogCategoriesHandler->getChildrenCategoriesIds($catalogCategory);
        $products = $this->pagination->changePageSize(21)->paginate(
            $this->productRepository->findProductsByCatalogAndManufacturerQuery($manufacturer, $selectedParameters, $catalogCategoriesIds),
            (int) $request->get('page', 1)
        )->getPaginator();

        $seo = $this->catalogSeoHandler->getCategorySeo($catalogCategory);
        if (null !== $seo) {
            $seo = $this->catalogSeoHandler->getCatalogSeo($seo, null, $manufacturer);
        }

        return $this->createResponse(
            $catalogCategory,
            $products,
            $selectedParameters,
            $seo,
            $manufacturer,
            null,
            $sort,
            $priceMin,
            $priceMax
        );
    }

    /**
     * @Route("/category/{category_alias}/purpose/{purpose_alias}", name="catalog_category_purpose_view")
     * @ParamConverter("catalogCategory", options={"mapping": {"category_alias": "alias"}}))
     * @ParamConverter("purpose", options={"mapping": {"purpose_alias": "alias"}}))
     */
    public function categoryPurposeView(
        Request $request,
        CatalogCategory $catalogCategory,
        Purpose $purpose
    ): Response {
        $selectedParameters = $this->preparationSelectedParameter($request->get('parameter_values', []));
        $sort = $request->get('sort', null);
        $priceMin = (int) $request->get('priceMin', 0);
        $priceMax = (int) $request->get('priceMax', self::PRICE_MAX);

        $catalogCategoriesIds = $this->catalogCategoriesHandler->getChildrenCategoriesIds($catalogCategory);
        $products = $this->pagination->changePageSize(21)->paginate(
            $this->productRepository->findProductsByCatalogAndPurposeQuery($purpose, $catalogCategoriesIds, $selectedParameters),
            (int) $request->get('page', 1)
        )->getPaginator();

        $seo = $this->catalogSeoHandler->getCategorySeo($catalogCategory);
        if (null !== $seo) {
            $seo = $this->catalogSeoHandler->getCatalogSeo($seo, $purpose, null);
        }

        return $this->createResponse(
            $catalogCategory,
            $products,
            $selectedParameters,
            $seo,
            null,
            $purpose,
            $sort,
            $priceMin,
            $priceMax
        );
    }

    /**
     * @Route("/category/{category_alias}/manufacturer/{manufacturer_alias}/purpose/{purpose_alias}", name="catalog_category_manufacturer_purpose_view")
     * @ParamConverter("catalogCategory", options={"mapping": {"category_alias": "alias"}}))
     * @ParamConverter("manufacturer", options={"mapping": {"manufacturer_alias": "alias"}}))
     * @ParamConverter("purpose", options={"mapping": {"purpose_alias": "alias"}}))
     */
    public function categoryManufacturerPurposeView(
        Request $request,
        CatalogCategory $catalogCategory,
        Manufacturer $manufacturer,
        Purpose $purpose
    ): Response {
        $selectedParameters = $this->preparationSelectedParameter($request->get('parameter_values', []));
        $sort = $request->get('sort', null);
        $priceMin = (int) $request->get('priceMin', 0);
        $priceMax = (int) $request->get('priceMax', self::PRICE_MAX);

        $catalogCategoriesIds = $this->catalogCategoriesHandler->getChildrenCategoriesIds($catalogCategory);
        $products = $this->pagination->changePageSize(21)->paginate(
            $this->productRepository->findProductsByCatalogAndManufacturerAndPurposeQuery(
                $manufacturer,
                $purpose,
                $catalogCategoriesIds,
                $selectedParameters
            ),
            (int) $request->get('page', 1)
        )->getPaginator();

        $seo = $this->catalogSeoHandler->getCategorySeo($catalogCategory);
        if (null !== $seo) {
            $seo = $this->catalogSeoHandler->getCatalogSeo($seo, $purpose, $manufacturer);
        }

        return $this->createResponse(
            $catalogCategory,
            $products,
            $selectedParameters,
            $seo,
            $manufacturer,
            $purpose,
            $sort,
            $priceMin,
            $priceMax
        );
    }

    private function createResponse(
        CatalogCategory $catalogCategory,
        Paginator $products,
        array $selectedParameters,
        $seo,
        $selectedManufacturer,
        $selectedPurpose,
        ?string $sort,
        $priceMin,
        $priceMax
    ): Response {
        $manufacturers = $this->manufacturerRepository->findAllByCatalogCategory($catalogCategory);
        $purposes = $this->purposeRepository->findAllByCatalogCategory($catalogCategory);
        $parameters = $this->productRelationsHandler->getParametersByCategory($catalogCategory);
        if (!$selectedParameters) {
            $priceMax = self::PRICE_MAX;
        }
        [$products, $maxPriceProduct] = $this->productCollectionDataTransformer->transformProducts($products->getQuery()->getResult(), $sort, $priceMin, $priceMax);

        return $this->render('site/catalog/category/products-list.html.twig', [
            'products' => $products,
            'catalogCategory' => $catalogCategory,
            'parameters' => $parameters,
            'parameterValues' => $this->productRelationsHandler->parameterValues($parameters),
            'selectedParameters' => $selectedParameters,
            'pagination' => $this->pagination->render('site/layout/pagination.html.twig'),
            'manufacturers' => $this->sortParameterBySelected($manufacturers, $selectedManufacturer),
            'selectedManufacturer' => $selectedManufacturer,
            'purposes' => $this->sortParameterBySelected($purposes, $selectedPurpose),
            'selectedPurpose' => $selectedPurpose,
            'childrenCategories' => $this->catalogCategoriesHandler->getChildrenCategories($catalogCategory),
            'seo' => $seo,
            'sort' => $sort,
            'priceMin' => $priceMin,
            'priceMax' => $priceMax < $maxPriceProduct ? $maxPriceProduct : $priceMax,
            'maxPriceProduct' => $maxPriceProduct,
            'breadcrumbs' => $this->catalogCategoriesHandler->generateBreadcrumbsByCategory($catalogCategory, $selectedManufacturer, $selectedPurpose),
            'title' => $this->catalogCategoriesHandler->generateTitleByCategory($catalogCategory, $selectedManufacturer, $selectedPurpose),
        ]);
    }

    private function sortParameterBySelected($arrayParameter, $element)
    {
        if (null === $element) {
            return $arrayParameter;
        }
        foreach ($arrayParameter as $key => $item) {
            if ($item === $element) {
                unset($arrayParameter[$key]);
            }
        }
        array_unshift($arrayParameter, $element);

        return $arrayParameter;
    }

    private function preparationSelectedParameter($selectedParameters): array
    {
        $arrayParameter = [];

        if (!$selectedParameters) {
            return $arrayParameter;
        }

        foreach ($selectedParameters as $key => $parameter) {
            if (!isset($arrayParameter[$key])) {
                $arrayParameter[$key] = array_keys($parameter);
            }
        }

        return $arrayParameter;
    }
}
