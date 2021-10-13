<?php declare(strict_types=1);

namespace App\Controller\Admin\Catalog\Purpose;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class PurposeFilterForm extends AbstractType
{
    /**
     * @param string[] $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Заголовок',
                'required' => false,
            ]);
    }
}
