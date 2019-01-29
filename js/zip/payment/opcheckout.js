if('Zip' in window && Zip.Checkout) {
    if('Review' in window) {
        Object.extend(
            Review.prototype, {

                reviewSave: Review.prototype.save,

                save: function () {
                    // support Zip Payment order placement
                    Zip.Checkout.placeOrder(this.reviewSave);

                }

            }
        );
    }
}