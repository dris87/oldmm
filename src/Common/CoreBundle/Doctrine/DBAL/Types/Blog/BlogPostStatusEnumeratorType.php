<?php

/*
 * This file is part of the `All4One/Ujallas.hu` project.
 *
 * (c) https://ujallas.hu
 *
 * Developed by: Ferencz Dávid Tamás <fdt0712@gmail.com>
 * Contributed: Sipos Zoltán <sipiszoty@gmail.com>, Pintér Szilárd <leaderlala00@gmail.com >
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Common\CoreBundle\Doctrine\DBAL\Types\Blog;

use Common\CoreBundle\Doctrine\DBAL\Types\AbstractEnumeratorType;
use Common\CoreBundle\Enumeration\Blog\BlogPostStatusEnum;

/**
 * Class BlogPostStatusEnumeratorType.
 */
class BlogPostStatusEnumeratorType extends AbstractEnumeratorType
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'blog_post_status_enum';
    }

    /**
     * @param int $value
     *
     * @return BlogPostStatusEnum|static
     */
    protected function createEntity($value)
    {
        return BlogPostStatusEnum::create($value);
    }
}
