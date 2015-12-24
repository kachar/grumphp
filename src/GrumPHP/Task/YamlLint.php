<?php

namespace GrumPHP\Task;

use GrumPHP\Exception\RuntimeException;
use GrumPHP\Linter\Yaml\YamlLinter;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class YamlLint
 *
 * @package GrumPHP\Task
 */
class YamlLint extends AbstractLinterTask
{
    /**
     * @var YamlLinter
     */
    protected $linter;

    /**
     * @return string
     */
    public function getName()
    {
        return 'yamllint';
    }

    /**
     * @return OptionsResolver
     */
    public function getConfigurableOptions()
    {
        $resolver = parent::getConfigurableOptions();
        $resolver->setDefaults(array(
            'object_support' => false,
            'exception_on_invalid_type' => false,
        ));

        $resolver->addAllowedTypes('object_support', array('bool'));
        $resolver->addAllowedTypes('exception_on_invalid_type', array('bool'));

        return $resolver;
    }

    /**
     * {@inheritdoc}
     */
    public function canRunInContext(ContextInterface $context)
    {
        return ($context instanceof GitPreCommitContext || $context instanceof RunContext);
    }

    /**
     * {@inheritdoc}
     */
    public function run(ContextInterface $context)
    {
        $files = $context->getFiles()->name('/\.(yaml|yml)$/i');
        if (0 === count($files)) {
            return;
        }

        $config = $this->getConfiguration();

        $this->linter->setObjectSupport($config['object_support']);
        $this->linter->setExceptionOnInvalidType($config['exception_on_invalid_type']);

        $lintErrors = $this->lint($files);
        if ($lintErrors->count()) {
            throw new RuntimeException($lintErrors->__toString());
        }
    }
}
