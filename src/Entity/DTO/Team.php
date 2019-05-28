<?php

namespace App\Entity\DTO;

use JMS\Serializer\Annotation\Type;
use Symfony\Component\Validator\Constraints as Assert;

class Team
{
    /**
     * @Type("string")
     * @Assert\NotBlank(
     *     groups={"team_add"},
     *     message="Team name should not be blank."
     * )
     * @Assert\Regex(
     *     groups={"team_add", "team_update"},
     *     pattern="/^[a-z0-9 .\-]+$/i",
     *     message="Team name should be type of {{ type }}."
     * )
     */
    public $name;

    /**
     * @Type("string")
     * @Assert\NotBlank(
     *     groups={"team_add"},
     *     message="Team strip should not be blank."
     * )
     * @Assert\Regex(
     *     groups={"team_add", "team_update"},
     *     pattern="/^[a-z0-9 .\-]+$/i",
     *     message="Team strip should be type of {{ type }}."
     * )
     */
    private $strip;

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return Team
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getStrip()
    {
        return $this->strip;
    }

    /**
     * @param mixed $strip
     * @return Team
     */
    public function setStrip($strip)
    {
        $this->strip = $strip;

        return $this;
    }
}