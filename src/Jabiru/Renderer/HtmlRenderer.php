<?php

namespace Scribe\Jabiru\Renderer;

use Scribe\Jabiru\Common\Element;
use Scribe\Jabiru\Common\Text;
use Scribe\Jabiru\Event\EmitterAwareInterface;
use Scribe\Jabiru\Event\EmitterAwareTrait;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Renders markdown result to HTML format
 */
class HtmlRenderer implements RendererInterface, EmitterAwareInterface
{
    use EmitterAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function renderParagraph($content, array $options = array())
    {
        $options = $this
            ->createResolver()
            ->resolve($options)
        ;

        $tag = (new Element('p'))
            ->setInner($content)
            ->setAttributes($options['attr'])
        ;

        $this->getEmitter()->emit('tag', [$tag]);

        return $tag->render();
    }

    /**
     * {@inheritdoc}
     */
    public function renderHeader($content, array $options = array())
    {
        $options = $this
            ->createResolver()
            ->setRequired(['level'])
            ->setAllowedValues(['level' => [1, 2, 3, 4, 5, 6]])
            ->resolve($options)
        ;

        $tag = (new Element('h' . $options['level']))
            ->setAttributes($options['attr'])
            ->setInner($content)
        ;

        $this->getEmitter()->emit('tag', [$tag]);

        return $tag->render();
    }

    /**
     * {@inheritdoc}
     */
    public function renderCodeBlock($content, array $options = array())
    {
        $content = ($content instanceof Text ? $content : new Text($content));

        $options = $this
            ->createResolver()
            ->resolve($options)
        ;

        $tagCode = (new Element('code'))
            ->setAttributes($options['attr'])
            ->setInner($content->append("\n"))
        ;
        $tagPre = (new Element('pre'))
            ->setInner($tagCode)
        ;

        $this->getEmitter()->emit('tag', [$tagPre]);

        return $tagPre->render();
    }

    /**
     * {@inheritdoc}
     */
    public function renderCodeSpan($content, array $options = array())
    {
        $tag = (new Element('code'))
            ->setType(Element::TYPE_INLINE)
            ->setInner($content)
        ;

        $this->getEmitter()->emit('tag', [$tag]);

        return $tag->render();
    }

    /**
     * @param string|Text $content
     * @param array       $options
     *
     * @return string
     */
    public function renderLink($content, array $options = array())
    {
        $options = $this
            ->createResolver()
            ->setRequired(['href'])
            ->setDefaults(['href' => '#', 'title' => ''])
            ->setAllowedTypes(['href' => 'string', 'title' => 'string'])
            ->resolve($options)
        ;

        $tag = (new Element('a'))
            ->setInner($content)
            ->setAttribute('href', $options['href'])
        ;

        if ($options['title']) {
            $tag->setAttribute('title', $options['title']);
        }

        $this->getEmitter()->emit('tag', [$tag]);

        return $tag->render();
    }

    /**
     * {@inheritdoc}
     */
    public function renderBlockQuote($content, array $options = array())
    {
        $tag = (new Element('blockquote'))
            ->setInner($content->wrap("\n", "\n"))
        ;

        $this->getEmitter()->emit('tag', [$tag]);

        return $tag->render();
    }

    /**
     * {@inheritdoc}
     */
    public function renderList($content, array $options = array())
    {
        $content = ($content instanceof Text ? $content : new Text($content));

        $options = $this->createResolver()
            ->setRequired(['type'])
            ->setAllowedValues(['type' => ['ul', 'ol']])
            ->setDefaults(['type' => 'ul'])
            ->resolve($options);

        $tag = (new Element($options['type']))
            ->setInner($content->prepend("\n"))
            ->setAttributes($options['attr'])
        ;

        $this->getEmitter()->emit('tag', [$tag]);

        return $tag->render();
    }

    /**
     * {@inheritdoc}
     */
    public function renderListItem($content, array $options = array())
    {
        $tag = (new Element('li'))
            ->setInner($content)
        ;

        $this->getEmitter()->emit('tag', [$tag]);

        return $tag->render();
    }

    /**
     * {@inheritdoc}
     */
    public function renderHorizontalRule(array $options = array())
    {
        $tag = (new Element('hr'))
            ->setType(Element::TYPE_INLINE)
            ->setEmptyTagSuffix($this->getEmptyTagSuffix())
        ;

        $this->getEmitter()->emit('tag', [$tag]);

        return $tag->render();
    }

    /**
     * {@inheritdoc}
     */
    public function renderImage($src, array $options = array())
    {
        $options = $this
            ->createResolver()
            ->resolve($options)
        ;

        $tag = (new Element('img'))
            ->setEmptyTagSuffix($this->getEmptyTagSuffix())
            ->setType(Element::TYPE_INLINE)
            ->setAttribute('src', $src)
            ->setAttributes($options['attr'])
        ;

        $this->getEmitter()->emit('tag', [$tag]);

        return $tag->render();
    }

    /**
     * {@inheritdoc}
     */
    public function renderBoldText($text, array $options = array())
    {
        $tag = (new Element('strong'))
            ->setInner($text)
        ;

        $this->getEmitter()->emit('tag', [$tag]);

        return $tag->render();
    }

    /**
     * {@inheritdoc}
     */
    public function renderItalicText($text, array $options = array())
    {
        $tag = (new Element('em'))
            ->setInner($text)
        ;

        $this->getEmitter()->emit('tag', [$tag]);

        return $tag->render();
    }

    /**
     * {@inheritdoc}
     */
    public function renderLineBreak(array $options = array())
    {
        $tag = (new Element('br'))
            ->setType(Element::TYPE_INLINE)
            ->setEmptyTagSuffix($this->getEmptyTagSuffix())
        ;

        $this
            ->getEmitter()
            ->emit('tag', [$tag])
        ;

        return $tag->render();
    }

    /**
     * {@inheritdoc}
     */
    public function renderTag($tagName, $content, $tagType = Element::TYPE_BLOCK, array $options = array())
    {
        $options = $this
            ->createResolver()
            ->resolve($options)
        ;

        $tag = (new Element($tagName))
            ->setType($tagType)
            ->setInner($content)
            ->setAttributes($options['attr'])
        ;

        $this
            ->getEmitter()
            ->emit('tag', [$tag])
        ;

        return $tag->render();
    }

    /**
     * @return OptionsResolver
     */
    protected function createResolver()
    {
        return (new OptionsResolver)
            ->setDefaults([
                'attr' => []
            ])
            ->setAllowedTypes([
                'attr' => 'array'
            ])
        ;
    }

    /**
     * @return string
     */
    protected function getEmptyTagSuffix()
    {
        return '>';
    }

}