<?php

namespace App\Form\Evaluation;

use App\Entity\EvalBudgetPerson;
use App\Form\Utils\Choices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class EvalBudgetPersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("ressources", ChoiceType::class, [
                "choices" => Choices::getChoices(Choices::YES_NO),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("ressourcesAmt", MoneyType::class, [
                "attr" => ["class" => "js-ressourcesAmt text-right"],
                "required" => false
            ])
            ->add("disAdultAlw")
            ->add("disChildAlw")
            ->add("unemplBenf")
            ->add("asylumSeekerAlw")
            ->add("tempWaitingAlw")
            ->add("familyAlw")
            ->add("solidarityAlw")
            ->add("paidTraining")
            ->add("youthGuarantee")
            ->add("maintenance")
            ->add("activityBonus")
            ->add("pensionBenf")
            ->add("minIncome")
            ->add("salary")
            ->add("ressourceOther", null, ["label_attr" => ["class" => "js-noText"]])
            ->add("ressourceOtherPrecision", null, ["attr" => ["placeholder" => "Autre ressource..."]])
            ->add("disAdultAlwAmt", MoneyType::class, [
                "attr" => ["class" => "js-ressources text-right"],
                "required" => false
            ])
            ->add("disChildAlwAmt", MoneyType::class, [
                "attr" => ["class" => "js-ressources text-right"],
                "required" => false
            ])
            ->add("unemplBenfAmt", MoneyType::class, [
                "attr" => ["class" => "js-ressources text-right"],
                "required" => false
            ])
            ->add("asylumSeekerAlwAmt", MoneyType::class, [
                "attr" => ["class" => "js-ressources text-right"],
                "required" => false
            ])
            ->add("tempWaitingAlwAmt", MoneyType::class, [
                "attr" => ["class" => "js-ressources text-right"],
                "required" => false
            ])
            ->add("familyAlwAmt", MoneyType::class, [
                "attr" => ["class" => "js-ressources text-right"],
                "required" => false
            ])
            ->add("solidarityAlwAmt", MoneyType::class, [
                "attr" => ["class" => "js-ressources text-right"],
                "required" => false
            ])
            ->add("paidTrainingAmt", MoneyType::class, [
                "attr" => ["class" => "js-ressources text-right"],
                "required" => false
            ])
            ->add("youthGuaranteeAmt", MoneyType::class, [
                "attr" => ["class" => "js-ressources text-right"],
                "required" => false
            ])
            ->add("maintenanceAmt", MoneyType::class, [
                "attr" => ["class" => "js-ressources text-right"],
                "required" => false
            ])
            ->add("activityBonusAmt", MoneyType::class, [
                "attr" => ["class" => "js-ressources text-right"],
                "required" => false
            ])
            ->add("pensionBenfAmt", MoneyType::class, [
                "attr" => ["class" => "js-ressources text-right"],
                "required" => false
            ])
            ->add("minIncomeAmt", MoneyType::class, [
                "attr" => ["class" => "js-ressources text-right"],
                "required" => false
            ])
            ->add("salaryAmt", MoneyType::class, [
                "attr" => ["class" => "js-ressources text-right"],
                "required" => false
            ])
            ->add("ressourceOtherAmt", MoneyType::class, [
                "attr" => ["class" => "js-ressources text-right"],
                "required" => false
            ])
            ->add("taxIncomeN1", MoneyType::class, [
                "attr" => ["class" => "text-right"],
                "required" => false
            ])
            ->add("taxIncomeN2", MoneyType::class, [
                "attr" => ["class" => "text-right"],
                "required" => false
            ])
            ->add("ressourcesComment")
            ->add("charges", ChoiceType::class, [
                "choices" => Choices::getChoices(Choices::YES_NO),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("chargesAmt", MoneyType::class, [
                "attr" => ["class" => "js-chargesAmt text-right"],
                "required" => false
            ])

            ->add("rent")
            ->add("electricityGas")
            ->add("water")
            ->add("insurance")
            ->add("mutual")
            ->add("taxes")
            ->add("transport")
            ->add("childcare")
            ->add("alimony")
            ->add("phone")
            ->add("chargeOther", null, ["label_attr" => ["class" => "js-noText"]])
            ->add("chargeOtherPrecision", null, ["attr" => ["placeholder" => "Autre charge..."]])
            ->add("rentAmt", MoneyType::class, [
                "attr" => ["class" => "js-charges text-right"],
                "required" => false
            ])
            ->add("electricityGasAmt", MoneyType::class, [
                "attr" => ["class" => "js-charges text-right"],
                "required" => false
            ])
            ->add("waterAmt", MoneyType::class, [
                "attr" => ["class" => "js-charges text-right"],
                "required" => false
            ])
            ->add("insuranceAmt", MoneyType::class, [
                "attr" => ["class" => "js-charges text-right"],
                "required" => false
            ])
            ->add("mutualAmt", MoneyType::class, [
                "attr" => ["class" => "js-charges text-right"],
                "required" => false
            ])
            ->add("taxesAmt", MoneyType::class, [
                "attr" => ["class" => "js-charges text-right"],
                "required" => false
            ])
            ->add("transportAmt", MoneyType::class, [
                "attr" => ["class" => "js-charges text-right"],
                "required" => false
            ])
            ->add("childcareAmt", MoneyType::class, [
                "attr" => ["class" => "js-charges text-right"],
                "required" => false
            ])
            ->add("alimonyAmt", MoneyType::class, [
                "attr" => ["class" => "js-charges text-right"],
                "required" => false
            ])
            ->add("phoneAmt", MoneyType::class, [
                "attr" => ["class" => "js-charges text-right"],
                "required" => false
            ])
            ->add("chargeOtherAmt", MoneyType::class, [
                "attr" => ["class" => "js-charges text-right"],
                "required" => false
            ])
            ->add("chargeComment")

            ->add("debts", ChoiceType::class, [
                "choices" => Choices::getChoices(Choices::YES_NO),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("debtsAmt", MoneyType::class, [
                "attr" => ["class" => "js-debtsAmt text-right"],
                "required" => false
            ])
            ->add("debtRental")
            ->add("debtConsrCredit")
            ->add("debtMortgage")
            ->add("debtFines")
            ->add("debtTaxDelays")
            ->add("debtBankOverdrafts")
            ->add("debtOther")
            ->add("debtOtherPrecision")
            ->add("debtComment")
            ->add("monthlyRepaymentAmt", MoneyType::class, [
                "attr" => ["class" => "js-repaymentAmt text-right"],
                "required" => false
            ])
            ->add("overIndebtRecord", ChoiceType::class, [
                "choices" => Choices::getChoices(EvalBudgetPerson::OVER_INDEBT_RECCORD),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("overIndebtRecordDate", DateType::class, [
                "widget" => "single_text",
                "required" => false
            ])
            ->add("settlementPlan", ChoiceType::class, [
                "choices" => Choices::getChoices(EvalBudgetPerson::SETTLEMENT_PLAN),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("moratorium", ChoiceType::class, [
                "choices" => Choices::getChoices(Choices::YES_NO_IN_PROGRESS),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("endRightsDate", DateType::class, [
                "widget" => "single_text",
                "required" => false,
                "help" => "Date de fin des prestations ou des allocations"
            ])
            ->add("commentEvalBudget", null, [
                "label_attr" => ["class" => "sr-only"],
                "attr" => [
                    "rows" => 5,
                    "placeholder" => "Write a comment about the budget situation"
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => EvalBudgetPerson::class,
            "translation_domain" => "budget"
        ]);
    }
}
