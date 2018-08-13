<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class <?= $class_name ?>.
 */
class <?= $class_name ?> extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'      => <?= $dtoShortName ?>::class,
                'csrf_protection' => false,
            ]
        );
    }

    /**
     * @return null|string
     */
    public function getBlockPrefix()
    {
        return '';
    }
}
