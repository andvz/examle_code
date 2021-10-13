<?php declare(strict_types=1);

namespace App\Controller\Admin\Catalog\Manufacturer;

use App\AdminFormTypes\EntityTreeFormType;
use App\Entity\Catalog\CatalogCategory;
use App\Entity\Catalog\Manufacturer;
use App\Repository\Catalog\CatalogCategoryRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ManufacturerForm extends AbstractType
{
    /**
     * @param string[] $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Manufacturer $manufacturer */
        $manufacturer = $options['data'];

        $builder
            ->add('title', TextType::class, [
                'label' => 'Заголовок',
                'attr' => ['class' => 'js-transliterate-source'],
            ])
            ->add('alias', TextType::class, [
                'label' => 'Алиас',
                'attr' => [
                    'class' => 'js-transliterate-purpose',
                    'help' => 'Формат: англ. буквы, цифры и -',
                ],
            ])
            ->add('catalogCategories', EntityTreeFormType::class, [
                'mapped' => false,
                'label' => 'Категория',
                'required' => true,
                'multiple' => true,
                'class' => CatalogCategory::class,
                'choice_label' => 'title',
                'attr' => [
                    'class' => 'select2',
                    'style' => 'width: 100%',
                ],
                'query_builder' => fn (CatalogCategoryRepository $repository) => $repository->childrenQueryBuilder()->andWhere('node.parent IS NOT NULL'),
                'data' => $manufacturer->getCatalogCategories(),
            ])
            ->add('description', TextType::class, [
                'label' => 'СЕО description',
            ])
            ->add('keywords', TextType::class, [
                'label' => 'СЕО keywords',
            ]);
    }
}
