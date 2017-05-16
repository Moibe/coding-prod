<?php

namespace AppBundle\Repository;

/**
 * ProductRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ProductRepository extends \Doctrine\ORM\EntityRepository {

    public function findOthers($item) {
        $q = $this->createQueryBuilder('e')
                ->addSelect('RAND() as HIDDEN rand')
                ->andWhere('e.id NOT IN (:ids)')
                ->andWhere('e.featerud = 1')
                ->setParameter('ids', array($item->getId()))
                ->addOrderBy('rand');

        return $q->getQuery()->setMaxResults(4)->execute();
    }

}
